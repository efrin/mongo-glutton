<?php


namespace Ephrin\Glutton\MongoDB\Tests;


class GluttonBase extends \PHPUnit_Framework_TestCase
{
    const SUFFIX = 'gltn';
    private $prefId;

    public function id()
    {
        return $this->prefId = new \MongoId();
    }

    public function getPrevId()
    {
        return $this->prefId;
    }

    protected static function suffix($e)
    {
        return self::SUFFIX . ucfirst($e);
    }

    protected function getClient()
    {
        return new \MongoClient();
    }

    protected function getSharedDB()
    {
        return $this->getClient()->selectDB('test');
    }

    protected function getCollection($name)
    {
        return $this->getSharedDB()->selectCollection(self::suffix($name));
    }


    public function getSourceCollection()
    {
        return $this->getCollection('source');
    }

    public function getTargetCollection()
    {
        return $this->getCollection('target');
    }

    public function createReferences($amount = 1)
    {
        return array_map(
            function () {
                list($ref) = $this->createReferencedDocument();
                return $ref;
            },
            array_fill(0, $amount, null)
        );
    }

    public function createReferencedDocument($sourceDocument = null, $refIsFull = true)
    {
        $sourceCollection = $this->getSourceCollection();
        $sourceId = new \MongoId();

        $identity = [
            '_id' => $sourceId,
            'data' => md5(rand(0, time()))
        ];

        if (is_array($sourceDocument)) {
            $sourceDocument = array_merge(
                $sourceDocument,
                $identity
            );
        } else {
            $sourceDocument = $identity;
        }

        $sourceCollection->save($sourceDocument);

        if ($refIsFull) {
            return [
                \MongoDBRef::create($sourceCollection->getName(), $sourceId, $sourceCollection->db),
                $sourceDocument
            ];
        } else {
            return [\MongoDBRef::create($sourceCollection->getName(), $sourceId), $sourceDocument];
        }
    }


}
 