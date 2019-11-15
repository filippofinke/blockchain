<?php
declare(strict_types = 1);

require __DIR__ . '/Logger.php';

class Block
{
    private $index;
    private $hash;
    private $previousHash;
    private $timestamp;
    private $data;
    private $difficulty;
    private $nonce;

    public function getIndex() : int
    {
        Logger::log("block", __FUNCTION__);
        return $this->index;
    }

    public function getHash() : string
    {
        Logger::log("block", __FUNCTION__);
        return $this->hash;
    }

    public function getPreviousHash() : string
    {
        Logger::log("block", __FUNCTION__);
        return $this->previousHash;
    }

    public function getTimestamp() : int
    {
        Logger::log("block", __FUNCTION__);
        return $this->timestamp;
    }

    public function getData() : array
    {
        Logger::log("block", __FUNCTION__);
        return $this->data;
    }

    public function getDifficulty() : int
    {
        Logger::log("block", __FUNCTION__);
        return $this->difficulty;
    }

    public function getNonce() : int
    {
        Logger::log("block", __FUNCTION__);
        return $this->nonce;
    }

    public function setHash(string $hash)
    {
        Logger::log("block", __FUNCTION__);
        $this->hash = $hash;
    }

    public function toArray()
    {
        Logger::log("block", __FUNCTION__);
        return array(
            "index" => $this->getIndex(),
            "hash" => $this->getHash(),
            "previous_hash" =>$this->getPreviousHash(),
            "timestamp" => $this->getTimestamp(),
            "data" => $this->getData(),
            "difficulty" => $this->getDifficulty(),
            "nonce" => $this->getNonce()
        );
    }

    public function toJson()
    {
        Logger::log("block", __FUNCTION__);
        return json_encode($this->toArray());
    }

    public static function calculateHash(int $index, string $previousHash, int $timestamp, array $data, int $difficulty, int $nonce) : string
    {
        Logger::log("block", __FUNCTION__);
        $data = json_encode($data);
        $string = $index.$previousHash.$timestamp.$data.$difficulty.$nonce;
        return hash("sha256", $string);
    }

    public static function calculateHashForBlock(Block $block) : string
    {
        Logger::log("block", __FUNCTION__);
        return self::calculateHash(
            $block->getIndex(),
            $block->getPreviousHash(),
            $block->getTimestamp(),
            $block->getData(),
            $block->getDifficulty(),
            $block->getNonce()
        );
    }

    public static function arrayToBlock(array $block) : Block
    {
        Logger::log("block", __FUNCTION__);
        if (self::isValidStructure($block)) {
            return new Block(
                $block["index"],
                $block["hash"],
                $block["previous_hash"],
                $block["timestamp"],
                $block["data"],
                $block["difficulty"],
                $block["nonce"]
            );
        }
        return null;
    }

    public static function isValidStructure(array $block) : bool
    {
        Logger::log("block", __FUNCTION__);
        if (isset($block["index"]) &&
           isset($block["hash"]) &&
           isset($block["previous_hash"]) &&
           isset($block["timestamp"]) &&
           isset($block["data"]) &&
           isset($block["difficulty"]) &&
           isset($block["nonce"])) {
            return true;
        }
        return false;
    }

    public function __construct(int $index, string $hash, string $previousHash, int $timestamp, array $data, int $difficulty, int $nonce)
    {
        Logger::log("block", __FUNCTION__);
        $this->index = $index;
        if ($hash === '') {
            $this->hash = Block::calculateHash(
                $index,
                $previousHash,
                $timestamp,
                $data,
                $difficulty,
                $nonce
           );
        } else {
            $this->hash = $hash;
        }
        $this->previousHash = $previousHash;
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->difficulty = $difficulty;
        $this->nonce = $nonce;
    }
}
