<?php


namespace Ephrin\Glutton\MongoDB;


use Ephrin\Glutton\MongoDB\Collection\AxonCollection;

class Glutton
{

    /**
     * @var \MongoClient
     */
    private $client;

    function __construct(\MongoClient $client)
    {
        $this->client = $client;
    }


    public function wrap(array $record)
    {
        return new AxonCollection($this, $record);
    }


    public function retrieve(AxonCollection $collection){


        return $collection;
    }









}