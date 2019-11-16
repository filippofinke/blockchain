<?php
namespace FilippoFinke;
use FilippoFinke\Block;

/**
 * Class that represent the blockchain.
 */
class Blockchain
{
    /**
     * Array that store the blockchain blocks.
     */
    private $blocks;

    /**
     * Getter method for the blockchain blocks.
     * 
     * @return array The blocks of the blockchain.
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Getter method for the genesis block.
     * 
     * @return Block The genesis block.
     */
    public function getGenesisBlock()
    {
        return $this->blocks[0];
    }

    /**
     * Getter method for the latest block.
     * 
     * @reuturn Block The last block of the blockchain.
     */
    public function getLatestBlock()
    {
        return end($this->blocks);
    }


    /**
     * Constructor method of the blockchain.
     * It generates the genesis block.
     */
    public function __construct()
    {
        $genesisBlock = new Block(
            0,
            null,
            null,
            time(),
            array(
                "message" => "This is the genesis block!"
            )
        );
        $this->blocks = [
            $genesisBlock
        ];
    }

    /**
     * Method used to verify if a new block is valid based on another block.
     * 
     * @param Block $newBlock The new block to be verified.
     * @param Block $previousBlock The previous block of the new block.
     * @return bool True if the block is valid otherwise false.
     */
    public function isValidNewBlock($newBlock, $previousBlock)
    {
        if ($previousBlock->getIndex() + 1 !== $newBlock->getIndex()) {
            return false;
        }

        if ($previousBlock->getHash() !== $newBlock->getPreviousHash()) {
            return false;
        }

        if ($newBlock->getHash() !== Block::calculateHashForBlock($newBlock)) {
            return false;
        }

        return true;
    }

    /**
     * Method used to verify the genesis block.
     * 
     * @param Block $genesisBlock The gensis block to verify.
     * @return bool True if the block is valid otherwise false.
     */
    public function isValidGenesis($genesisBlock)
    {
        if ($this->getGenesisBlock()->toJson() === $genesisBlock->toJson()) {
            return true;
        }
        return false;
    }

    /**
     * Method used to verify an entire chain.
     * 
     * @param array $blocks The chain as array.
     * @return bool True if the chain is valid otherwise false.
     */
    public function isValidChain($blocks)
    {
        if (!$this->isValidGenesis($blocks[0])) {
            return false;
        }
        for ($i = 1; $i < count($blocks); $i++) {
            if (!$this->isValidNewBlock($blocks[$i], $blocks[$i - 1])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Method used to replace the current chain with another one.
     * If the new blockchain is valid and bigger than the current one
     * it will replace the current one.
     * 
     * @param array $blocks The chain as array.
     * @return bool True if the chain has been replace otherwise false.
     */
    public function replaceChain($blocks)
    {
        if ($this->isValidChain($blocks) &&
            count($this->getBlocks()) < count($blocks)) {
            $this->blocks = $blocks;
            return true;
        }
        return false;
    }

    /**
     * Method used to generate a new block.
     * 
     * @param array $data The data to store into the block.
     * @return Block The block generated or null.
     */
    public function generateNextBlock($data)
    {
        $previousBlock = $this->getLatestBlock();
        $block = new Block(
            $previousBlock->getIndex() + 1,
            null,
            $previousBlock->getHash(),
            time(),
            $data
        );
        if ($this->addBlock($block)) {
            return $block;
        } else {
            return null;
        }
    }

    /**
     * Method used to add a block to the chain.
     * 
     * @param Block $newBlock The block to add.
     * @return bool True if the block has been added otherwise false.
     */
    public function addBlock($newBlock)
    {
        if ($this->isValidNewBlock($newBlock, $this->getLatestBlock())) {
            $this->blocks[] = $newBlock;
            return true;
        }
        return false;
    }
}
