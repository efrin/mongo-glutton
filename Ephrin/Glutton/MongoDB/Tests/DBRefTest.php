<?php


namespace Ephrin\Glutton\MongoDB\Tests;


class DBRefTest extends \PHPUnit_Framework_TestCase {

    public function getSharedConnection(){
        return new \MongoClient();
    }

    public function testOne(){
        $connection = $this->getSharedConnection();


        $db = $connection->selectDB('test');

        $collection = $db->selectCollection('ttt');
        $id = new \MongoId();
        $doc = ['_id' => $id, 'start' => microtime(1), 'type' => __METHOD__];
        $collection->insert($doc, ['w' => 1]);


        $doc_stored = $collection->findOne(['_id'=>$id]);

        $this->assertEquals($doc, $doc_stored);

    }

}
 