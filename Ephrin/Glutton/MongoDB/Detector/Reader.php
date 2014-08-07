<?php


namespace Ephrin\Glutton\MongoDB\Detector;


use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class Reader
 * @package Ephrin\Glutton\MongoDB\Detector
 */
class Reader
{

    private $started = false;

    protected $references;
    protected $levels;
    protected $path;

    private function pushLevel($key, $value, $isObject = false)
    {
        if (!$isObject) {
            $key = '[' . $key . ']';
        }
        $this->path[] = $key;
        array_walk($value, $this);
        array_pop($this->path);
    }

    public function reducePath(&$result, $item)
    {
        if (strpos($item, '[') === 0) {
            $result .= $item;
        } else {
            $result .= (empty($result) ? '' : '.') . $item;
        }

        return $result;
    }

    private function getPath($key, $isObject = false)
    {
        if (!$isObject) {
            $key = '[' . $key . ']';
        } else {
            $key = '.' . $key;
        }

        if (!empty($this->path)) {
            return array_reduce($this->path, [$this, 'reducePath'], '') . $key;
        } else {
            return $key;
        }

    }

    public static function simplifyReference($reference)
    {
        return \MongoDBRef::create(
            $reference['$ref'],
            $reference['$id'],
            isset($reference['$db']) ? $reference['$db'] : null
        );

    }

    private function pushReference($key, $reference)
    {
        $this->references[$this->getPath($key)] = self::simplifyReference($reference);

        //...scan deep
        $this->pushLevel($key, $reference);

    }

    function __invoke($value, $key)
    {
        if (is_array($value)) {
            if (\MongoDBRef::isRef($value)) {
                $this->pushReference($key, $value);
            } else {
                $this->pushLevel($key, $value);
            }
        }
    }

    protected function walk_object($object)
    {
        //todo implement
        throw new \RuntimeException('objects scan is not supported');
    }

    public function walk($record)
    {
        if (!$this->started) {
            $this->clear();
            $this->started = true;
        } else {
            throw new \RuntimeException('Reader was already started. To run new scan please invoke clear() method');
        }

        if (is_array($record)) {
            if (\MongoDBRef::isRef($record)) {
                $this->references['*'] = self::simplifyReference($record);
            }
            array_walk($record, $this);
        } elseif (is_object($record)) {
            $this->walk_object($record);
        } else {
            throw new \InvalidArgumentException('Record for scan must be an object or an array');
        }

    }

    public static function retrieve($document, $path, $default = null)
    {
        static $accessor;
        if ($path === '*') {
            return self::simplifyReference($document);
        } else {
            try {
                if (!$accessor) {
                    $accessor = PropertyAccess::createPropertyAccessor();
                }

                $value = $accessor->getValue($document, $path);

            } catch (\Exception $e) {
                //todo okify
                $value = $default;
            }
            return $value;
        }
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Clears all data scanned from document before
     */
    public function clear()
    {
        $this->references = $this->levels = $this->path = [];
        $this->started = false;
    }


} 