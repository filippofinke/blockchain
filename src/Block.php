<?php
namespace FilippoFinke;

/**
 * Class that represent a single block of the blockchain.
 */
class Block
{
    /**
     * Block index.
     */
    private $index;
    /**
     * Block hash.
     */
    private $hash;
    /**
     * Previous block hash.
     */
    private $previousHash;
    /**
     * Block timestamp.
     */
    private $timestamp;
    /**
     * Block data.
     */
    private $data;

    /**
     * Getter method for the block index.
     * 
     * @return int The index of the block.
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Getter method for the block hash.
     * 
     * @return string The hash of the block.
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Getter method for the block previous hash.
     * 
     * @return string The previous hash of the block.
     */
    public function getPreviousHash()
    {
        return $this->previousHash;
    }

    /**
     * Getter method for the block timestamp.
     * 
     * @return int The creation timestamp of the block.
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Getter method for the block data.
     * 
     * @return array The data stored into the block.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Block constructor.
     * 
     * @param int $index The index of the block. 
     * @param string $hash The hash of the block or null.
     * @param string $previousHash The hash of the previous block. 
     * @param int $timestamp The creation timestamp of the block. 
     * @param array $data The data of the block.
     */
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

    /**
     * Method used to convert the block to an array.
     * 
     * @return array The block as array.
     */
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

    /**
     * Method used to convert the block to a json string.
     * 
     * @return array The block as json string.
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Override of the __toString method.
     * 
     * @return array The block as json string.
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Method used to calculate the hash for a block.
     * 
     * @param int $index The index of the block. 
     * @param string $previousHash The hash of the previous block. 
     * @param int $timestamp The creation timestamp of the block. 
     * @param array $data The data of the block.
     * @return string A sha256 hash of the block.
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

    /**
     * Method used to calculate the hash for a block.
     * 
     * @param Block $block The block.
     * @return string A sha256 hash of the block.
     */
    public static function calculateHashForBlock($block)
    {
        return self::calculateHash(
            $block->getIndex(),
            $block->getPreviousHash(),
            $block->getTimestamp(),
            $block->getData()
        );
    }

    /**
     * Method that validate the structure of an array.
     * 
     * @param array The block as array.
     * @return bool If the structure is valid or not.
     */
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

    /**
     * Method used to convert a json string to a block.
     * 
     * @param string $json The json string to be converted.
     * @return Block A block if the string has been converted otherwise null.
     */
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
