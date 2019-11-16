<?php
namespace FilippoFinke;

use FilippoFinke\Block;

class Blockchain
{
    private $blocks;

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getGenesisBlock()
    {
        return $this->blocks[0];
    }

    public function getLatestBlock()
    {
        return end($this->blocks);
    }


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

    public function isValidGenesis($genesisBlock)
    {
        if ($this->getGenesisBlock()->toJson() === $genesisBlock->toJson()) {
            return true;
        }
        return false;
    }

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

    public function replaceChain($blocks)
    {
        if ($this->isValidChain($blocks) &&
            count($this->getBlocks()) < count($blocks)) {
            $this->blocks = $blocks;
            return true;
        }
        return false;
    }

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

    public function addBlock($newBlock)
    {
        if ($this->isValidNewBlock($newBlock, $this->getLatestBlock())) {
            $this->blocks[] = $newBlock;
            return true;
        }
        return false;
    }
}
