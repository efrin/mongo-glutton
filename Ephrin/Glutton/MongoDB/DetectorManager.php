<?php


namespace Ephrin\Glutton\MongoDB;


class DetectorManager
{
    /**
     * @var \MongoCollection
     */
    private $collection;

    function __construct(\MongoCollection $collection = null)
    {
        $this->collection = $collection;
    }


}