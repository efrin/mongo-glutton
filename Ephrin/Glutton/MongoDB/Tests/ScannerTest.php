<?php


namespace Ephrin\Glutton\MongoDB\Tests;


use Ephrin\Glutton\MongoDB\Detector\Scan;

class ScannerTest extends GluttonBase
{


    protected function setUp()
    {
        $this->getCollection('simpleSource')->drop();
        $this->getCollection('simpleTarget')->drop();
    }

    public function detections()
    {
        return array_map(
            function ($c) {
                return call_user_func($c);
            },
            [
                'simple' => function () {
                        list($ref) = $this->createReferencedDocument();

                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            [
                                '_id' => $id,
                                'ref' => $ref,
                                'createdBy' => __METHOD__
                            ]
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        return [
                            $fetched,
                            $scan->getReferences(),
                            array_combine(['[ref]'], [$ref])
                        ];
                    },
                'exoticOwning' => function () {
                        list($ref1, $ref2) = $refs = $this->createReferences(2);

                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            array_merge(
                                [
                                    '_id' => $id,
                                    'ref' => $ref2,
                                    'createdBy' => __METHOD__
                                ],
                                $ref1
                            )
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        $foundReferences = $scan->getReferences();

                        return [
                            $fetched,
                            $foundReferences,
                            array_combine(['*', '[ref]'], $refs)
                        ];

                    },
                'nesting' => function(){
                        list($ref1, $ref2) = $refs = $this->createReferences(2);

                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            [
                                '_id' => $id,
                                'refs' => [
                                    'a' => $ref1,
                                    'b' => $ref2
                                ],
                                'createdBy' => __METHOD__
                            ]
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        $foundReferences = $scan->getReferences();
                        return [
                            $fetched,
                            $foundReferences,
                            array_combine(['[refs][a]', '[refs][b]'], $refs)
                        ];
                    },
                'deepNesting' => function(){
                        list($ref1, $ref2) = $refs = $this->createReferences(2);

                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            [
                                '_id' => $id,
                                'refs' => [
                                    'a' => [
                                        'b' => $ref1
                                    ],
                                    'c' => $ref2
                                ],
                                'createdBy' => __METHOD__
                            ]
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        $foundReferences = $scan->getReferences();

                        return [
                            $fetched,
                            $foundReferences,
                            array_combine(['[refs][a][b]', '[refs][c]'], $refs)
                        ];

                    },
                'array' => function(){
                        list($ref1, $ref2) = $refs = $this->createReferences(2);
                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            [
                                '_id' => $id,
                                'refs' => [$ref1, $ref2],
                                'createdBy' => __METHOD__
                            ]
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        $foundReferences = $scan->getReferences();
                        return [
                            $fetched,
                            $foundReferences,
                            array_combine(['[refs][0]', '[refs][1]'], $refs)
                        ];
                    },
                'arrayWithNested' => function(){
                        list($ref1, $ref2, $ref3, $ref4) = $refs = $this->createReferences(4);
                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            [
                                '_id' => $id,
                                'refs' => [
                                    [
                                        $ref1,
                                        [
                                            'a' => $ref2,
                                            'b' => $ref3
                                        ]
                                    ],
                                    $ref4
                                ],
                                'createdBy' => __METHOD__
                            ]
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        $foundReferences = $scan->getReferences();
                        return [
                            $fetched,
                            $foundReferences,
                            array_combine(['[refs][0][0]','[refs][0][1][a]', '[refs][0][1][b]', '[refs][1]'], $refs)
                        ];
                    },
                'refsContainsRefsContainsRefsAndArrayOfRefs' => function(){
                        list($ref1, $ref2, $ref3, $ref4) = $this->createReferences(4);
                        $ref3['array'] = [
                            $ref4
                        ];
                        $ref2['in'] = $ref3;
                        $ref1['in'] = $ref2;

                        $refs = [$ref1, $ref2, $ref3, $ref4];


                        $id = new \MongoId();

                        $this->getTargetCollection()->save(
                            [
                                '_id' => $id,
                                'ref1' => $ref1,
                                'createdBy' => __METHOD__
                            ]
                        );

                        $fetched = $this->getTargetCollection()->findOne(['_id' => $id]);

                        $scan = new Scan();

                        $scan->walk($fetched);

                        $foundReferences = $scan->getReferences();
                        return [
                            $fetched,
                            $foundReferences,
                            array_combine(['[ref1]', '[ref1][in]', '[ref1][in][in]', '[ref1][in][in][array][0]'], $refs)
                        ];
                    }
            ]
        );
    }


    /**
     * @dataProvider detections
     */
    public function testVariations($fetched, $foundReferences, $expected)
    {
        $this->assertEquals(count($expected), count($foundReferences));

        foreach ($expected as $path => $reference) {
            $this->assertEquals($reference, Scan::retrieve($fetched, $path));

            $this->assertArrayHasKey($path, $foundReferences);

            $this->assertEquals($foundReferences[$path], Scan::simplifyReference($reference));
        }
    }


    public function testIsRefCanBeObj()
    {

        $ref = $this->createReferencedDocument();

        $refObj = (object)$ref;

        $this->assertFalse(
            \MongoDBRef::isRef($refObj),
            'Be aware! Now driver could parse objects as references. See isRef() usage in scanner.'
        );


    }

}