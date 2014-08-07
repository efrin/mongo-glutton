<?php


namespace Ephrin\Glutton\MongoDB\Tests;


class NativeReferencesTest extends GluttonBase
{
    protected function setUp()
    {
        $this->getCollection('simpleSource')->drop();
        $this->getCollection('simpleTarget')->drop();
    }


    public function testFullDbRef(){
        list($ref) = $this->createReferencedDocument([], true);
        $this->assertTrue(\MongoDBRef::isRef($ref));
        $this->assertArrayHasKey('$db', $ref);
    }

    public function testPartialDBRef(){
        list($ref) = $this->createReferencedDocument([], false);
        $this->assertTrue(\MongoDBRef::isRef($ref));
        $this->assertArrayNotHasKey('$db', $ref);
    }

    public function testSimpleDBRef(){

        $targetCollection = $this->getCollection('simpleTarget');


        $extends = [];
        $targetId = new \MongoId();
        list($reference, $sourceDocument) = $this->createReferencedDocument($extends, true);
        $targetDocument = [
            '_id' => $targetId,
            'ref' => $reference,
            'text' => 'amasource'
        ];

        $targetCollection->save($targetDocument);

        $td = $targetCollection->findOne(['_id' => $targetId]);

        $this->assertTrue(\MongoDBRef::isRef($td['ref']));

        $sd = $this->getSourceCollection()->getDBRef($td['ref']);
        $this->assertEquals($sourceDocument, $sd);

    }

    public function testExtendedDBRef(){

        $targetCollection = $this->getCollection('simpleTarget');

        $targetId = new \MongoId();
        $extends = [];
        list($ref, $sourceDocument) = $this->createReferencedDocument($extends, true);

        //extending by embedding some data
        $ref['data'] = $sourceDocument['data'];
        $targetDocument = [
            '_id' => $targetId,
            'ref' => $ref,
            'text' => 'amasource'
        ];

        $targetCollection->save($targetDocument);

        $targetDocumentFromDB = $targetCollection->findOne(['_id' => $targetId]);

        $this->assertTrue(\MongoDBRef::isRef($targetDocumentFromDB['ref']));

        $sourceDocumentFromDB = $this->getSourceCollection()->getDBRef($targetDocumentFromDB['ref']);

        $this->assertEquals($sourceDocument, $sourceDocumentFromDB);

        $this->assertEquals($sourceDocumentFromDB['data'], $targetDocumentFromDB['ref']['data']);
    }

} 