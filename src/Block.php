<?php
namespace FilippoFinke;

class Block
{
    private $index;
    private $hash;
    private $previousHash;
    private $timestamp;
    private $data;

    public function getIndex()
    {
        return $this->index;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getData()
    {
        return $this->data;
    }

    public function __construct(
        $index,
        $hash,
        $previousHash,
        $timestamp,
        $data
    ) {
        $this->index = $index;
        if ($hash === null) {
            $this->hash = self::calculateHash(
                $index,
                $previousHash,
                $timestamp,
                $data
            );
        } else {
            $this->hash = $hash;
        }
        $this->previousHash = $previousHash;
        $this->timestamp = $timestamp;
        $this->data = $data;
    }

    public function toArray()
    {
        return array(
            "index" => $this->index,
            "hash" => $this->hash,
            "previousHash" => $this->previousHash,
            "timestamp" => $this->timestamp,
            "data" => $this->data
        );
    }
    
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function __toString()
    {
        return $this->toJson();
    }


    /**
     * Helper functions
     */

    public static function calculateHash(
        $index,
        $previousHash,
        $timestamp,
        $data
    ) {
        $data = json_encode($data);
        $string = $index.$previousHash.$timestamp.$data;
        return hash("sha256", $string);
    }

    public static function calculateHashForBlock($block)
    {
        return self::calculateHash(
            $block->getIndex(),
            $block->getPreviousHash(),
            $block->getTimestamp(),
            $block->getData()
        );
    }

    public static function isValidStructure($array)
    {
        if (isset($block["index"]) &&
            isset($block["hash"]) &&
            isset($block["previousHash"]) &&
            isset($block["timestamp"]) &&
            isset($block["data"])
            ) {
            return true;
        }
        return false;
    }

    public static function jsonToBlock($json)
    {
        $blockAsArray = json_decode($json, true);
        if (self::isValidStructure($blockAsArray)) {
            return new Block(
                $blockAsArray["index"],
                $blockAsArray["hash"],
                $blockAsArray["previousHash"],
                $blockAsArray["timestamp"],
                $blockAsArray["data"]
            );
        }
        return null;
    }
}
