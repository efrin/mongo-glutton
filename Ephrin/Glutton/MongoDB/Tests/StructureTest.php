<?php


namespace Ephrin\Glutton\MongoDB\Tests;


class StructureTest extends GluttonBase
{

    protected function setUp()
    {
        $this->getCollection('simpleSource')->drop();
        $this->getCollection('simpleTarget')->drop();
    }

    public function testObjects()
    {
        $id = $this->id();
        $objectPrototype = [
            'prop_str' => 'string',
            'prop_arr' => [3, 2, 1]
        ];


        $po = (object)$objectPrototype;


        $data = [
            '_id' => $id,
            'simpleObject' => (object)$objectPrototype,
            'objectWithPrivateProperty' => $po
        ];
        $this->getTargetCollection()->save($data);

        $doc = $this->getTargetCollection()->findOne(['_id' => $id]);

        $this->assertEquals($objectPrototype, $doc['simpleObject']);
        $this->assertEquals($objectPrototype, $doc['objectWithPrivateProperty']);

    }
} 