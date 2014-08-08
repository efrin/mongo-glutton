<?php


namespace Ephrin\Glutton\MongoDB\Collection;


use Ephrin\Glutton\MongoDB\Detector\Reader;
use Ephrin\Glutton\MongoDB\Glutton;

class AxonCollection implements \ArrayAccess /*, \Traversable, \Iterator*/
{
    /**
     * @var AxonCollection || null
     */
    private $_parent;
    private $_position;
    private $_elements = [];
    private $_reference = false;
    private $_loaded = false;
    private $_glutton;

    function __construct(Glutton $glutton, $data = null, AxonCollection $parent = null, $position = null)
    {
        $this->_glutton = $glutton;

        if(\MongoDBRef::isRef($data)){
            $this->_reference = Reader::simplifyReference($data);
            $data = array_diff_key($data, $this->_reference);
        }

        $this->_elements = $data;
        $this->_parent = $parent;
        $this->_position = $position;

    }


    protected function isReference()
    {
        return $this->_reference;
    }

    protected function belongsToReference()
    {
        return $this->_parent && ($this->_parent->isReference() || $this->_parent->belongsToReference());
    }

    protected function isLoaded()
    {
        return $this->_loaded;
    }

    protected function load()
    {
        //load schema

        //stare level

        $this->_loaded = true;


        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        if (array_key_exists($offset, $this->_elements)) {
            return true;
        } elseif ($this->isReference() && !$this->isLoaded()) {
            return $this->load()->offsetExists($offset);
        } elseif ($this->belongsToReference()){
            return isset($this->_parent[$this->_position][$offset]);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }


//    /**
//     * (PHP 5 &gt;= 5.0.0)<br/>
//     * Return the current element
//     * @link http://php.net/manual/en/iterator.current.php
//     * @return mixed Can return any type.
//     */
//    public function current()
//    {
//        // TODO: Implement current() method.
//    }
//
//    /**
//     * (PHP 5 &gt;= 5.0.0)<br/>
//     * Move forward to next element
//     * @link http://php.net/manual/en/iterator.next.php
//     * @return void Any returned value is ignored.
//     */
//    public function next()
//    {
//        // TODO: Implement next() method.
//    }
//
//    /**
//     * (PHP 5 &gt;= 5.0.0)<br/>
//     * Return the key of the current element
//     * @link http://php.net/manual/en/iterator.key.php
//     * @return mixed scalar on success, or null on failure.
//     */
//    public function key()
//    {
//        // TODO: Implement key() method.
//    }
//
//    /**
//     * (PHP 5 &gt;= 5.0.0)<br/>
//     * Checks if current position is valid
//     * @link http://php.net/manual/en/iterator.valid.php
//     * @return boolean The return value will be casted to boolean and then evaluated.
//     * Returns true on success or false on failure.
//     */
//    public function valid()
//    {
//        // TODO: Implement valid() method.
//    }
//
//    /**
//     * (PHP 5 &gt;= 5.0.0)<br/>
//     * Rewind the Iterator to the first element
//     * @link http://php.net/manual/en/iterator.rewind.php
//     * @return void Any returned value is ignored.
//     */
//    public function rewind()
//    {
//        // TODO: Implement rewind() method.
//    }


}