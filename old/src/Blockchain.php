<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Block.php';

use Amp\Loop;
use Amp\Socket\ResourceSocket;
use Amp\Socket\Server;
use function Amp\asyncCall;
use function Amp\Socket\connect;

class Blockchain
{
    const BLOCK_GENERATION_INTERVAL = 10; // every x seconds
    const DIFFICULTY_ADJUSTMENT_INTERVAL = 10; // every x blocks

    // P2P
    const LATEST_BLOCK = 0;
    const ALL_BLOCKS = 1;
    const BLOCK_CHAIN = 2;


    // CONTROL
    const MINE = 3;
    const ADD_PEER = 4;
    const GET_LAST_BLOCK = 5;
    const GET_BLOCKCHAIN = 6;
    const GET_INFO = 7;

    const DELIMITER = "\r\n";

    private $peers;

    private $port;

    private $genesisBlock;

    private $blocks;

    private $peerToPeer;

    public function getPeers() : array
    {
        Logger::log("blockchain", __FUNCTION__);
        $peers = [];
        foreach ($this->peers as $peer) {
            $peers[] = (string)$peer->getRemoteAddress();
        }
        return $peers;
    }

    public function getBlocks() : array
    {
        Logger::log("blockchain", __FUNCTION__);
        return $this->blocks;
    }

    public function getBlocksAsArray()
    {
        Logger::log("blockchain", __FUNCTION__);
        $blocks = [];
        foreach ($this->blocks as $block) {
            $blocks[] = $block->toArray();
        }
        return $blocks;
    }

    public function getLatestBlock() : Block
    {
        Logger::log("blockchain", __FUNCTION__);
        $last = end($this->blocks);
        return $last;
    }

    public function getDifficulty($chain) : int
    {
        Logger::log("blockchain", __FUNCTION__);
        $latestBlock = end($chain);
        if ($latestBlock->getIndex() % self::DIFFICULTY_ADJUSTMENT_INTERVAL === 0 && $latestBlock->getIndex() !== 0) {
            return $this->getAdjustedDifficulty($latestBlock, $chain);
        } else {
            return $latestBlock->getDifficulty();
        }
    }

    public function getAdjustedDifficulty(Block $latestBlock, array $chain) : int
    {
        Logger::log("blockchain", __FUNCTION__);
        $prevAdjustmentBlock = $chain[count($chain) - self::DIFFICULTY_ADJUSTMENT_INTERVAL];
        $timeExpected = self::BLOCK_GENERATION_INTERVAL * self::DIFFICULTY_ADJUSTMENT_INTERVAL;
        $timeTaken = $latestBlock->getTimestamp() - $prevAdjustmentBlock->getTimestamp();
        if ($timeTaken < $timeExpected / 2) {
            return $prevAdjustmentBlock->getDifficulty() + 1;
        } elseif ($timeTaken > $timeExpected * 2) {
            return $prevAdjustmentBlock->getDifficulty() - 1;
        } else {
            return $prevAdjustmentBlock->getDifficulty();
        }
    }
    

    public function isValidNewBlock(Block $newBlock, Block $previousBlock) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        if ($previousBlock->getIndex() + 1 != $newBlock->getIndex()) {
            Logger::log("blockchain", __FUNCTION__ . "Block with an invalid index!");
            return false;
        }

        if ($previousBlock->getHash() != $newBlock->getPreviousHash()) {
            Logger::log("blockchain", __FUNCTION__ . "Block with wrong previous hash!");
            return false;
        }

        if ($newBlock->getHash() != ($expected = Block::calculateHashForBlock($newBlock))) {
            Logger::log("blockchain", __FUNCTION__ . "Block with wrong hash!");
            return false;
        }

        if (!$this->isValidTimestamp($newBlock, $previousBlock)) {
            return false;
        }

        if (!$this->hasValidHash($newBlock)) {
            return false;
        }

        return true;
    }

    public function isValidTimestamp(Block $newBlock, Block $previousBlock) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        return ($previousBlock->getTimestamp() - 60 < $newBlock->getTimestamp()
            && $newBlock->getTimestamp() - 60 < time());
    }

    public function hasValidHash(Block $block) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        if (!$this->hashMatchesBlockContent($block)) {
            return false;
        }
    
        if (!$this->hashMatchesDifficulty($block->getHash(), $block->getDifficulty())) {
            return false;
        }
        return true;
    }

    public function hashMatchesBlockContent(Block $block) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        return Block::calculateHashForBlock($block) === $block->getHash();
    }

    public function getAccumulatedDifficulty(array $chain) : int
    {
        Logger::log("blockchain", __FUNCTION__);
        $difficulty = 0;
        foreach ($chain as $block) {
            $difficulty += pow(2, $block->getDifficulty());
        }
        return $difficulty;
    }

    public function isValidGenesis(Block $genesisBlock) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        if ($genesisBlock->toJson() === $this->genesisBlock->toJson()) {
            return true;
        }
        return false;
    }

    public function isValidChain(array $blocks) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        if (!$this->isValidGenesis($blocks[0])) {
            Logger::log("blockchain", __FUNCTION__." Invalid genesis block!");
            return false;
        }
        for ($i = 1; $i < count($blocks); $i++) {
            if (!$this->isValidNewBlock($blocks[$i], $blocks[$i - 1])) {
                return false;
            }
        }
        return true;
    }

    public function replaceChain(array $blocks)
    {
        Logger::log("blockchain", __FUNCTION__);
        if ($this->isValidChain($blocks) && $this->getAccumulatedDifficulty($blocks) > $this->getAccumulatedDifficulty($this->getBlocks())) {
            $this->blocks = $blocks;
            Logger::log("blockchain", __FUNCTION__ . " Replaced current blockchain!");
            $this->broadcast(array(
                "type" => self::BLOCK_CHAIN,
                "data" => array($this->getLatestBlock()->toArray())
            ));
            return true;
        }
        Logger::log("blockchain", __FUNCTION__ . " Received an invalid blockchain!");
        return false;
    }

    public function generateNextBlock(array $data) : Block
    {
        Logger::log("blockchain", __FUNCTION__);
        $previousBlock = $this->getLatestBlock();
        $block = $this->findBlock(
            $previousBlock->getIndex() + 1,
            $previousBlock->getHash(),
            time(),
            $data,
            $this->getDifficulty($this->getBlocks())
        );
        $this->addBlock($block);
        $this->broadcast(array(
            "type" => self::BLOCK_CHAIN,
            "data" => array($this->getLatestBlock()->toArray())
        ));
        return $block;
    }

    public function findBlock($index, $previousHash, $time, $data, $difficulty) : Block
    {
        Logger::log("blockchain", __FUNCTION__);
        $nonce = 0;
        while (true) {
            Logger::log("blockchain", __FUNCTION__ . " Trying with $nonce");
            $hash =  Block::calculateHash($index, $previousHash, $time, $data, $difficulty, $nonce);
            if ($this->hashMatchesDifficulty($hash, $difficulty)) {
                Logger::log("blockchain", __FUNCTION__ . " Block found with nonce = $nonce");
                return new Block($index, '', $previousHash, $time, $data, $difficulty, $nonce);
            }
            $nonce++;
        }
    }

    public function hashMatchesDifficulty($hash, $difficulty)
    {
        Logger::log("blockchain", __FUNCTION__);
        $binary = "";
        $characters = str_split($hash);
        foreach ($characters as $character) {
            $data = unpack('H*', $character);
            $binary .= base_convert($data[1], 16, 2);
        }
        $prefix = str_repeat("0", $difficulty);
        return (substr($binary, 0, strlen($prefix)) === $prefix);
    }

    public function addBlock(Block $newBlock) : bool
    {
        Logger::log("blockchain", __FUNCTION__);
        if ($this->isValidNewBlock($newBlock, $this->getLatestBlock())) {
            $this->blocks[] = $newBlock;
            return true;
        }
        return false;
    }

    public function handleBlockchain(array $blocks)
    {
        Logger::log("blockchain", __FUNCTION__);
        if (count($blocks) === 0) {
            Logger::log("blockchain", __FUNCTION__." Received an empty blockchain!");
            return;
        }

        $lastReceived = end($blocks);
        if (!Block::isValidStructure($lastReceived)) {
            Logger::log("blockchain", __FUNCTION__." Invalid block structure!");
            return;
        }
        $lastBlockReceived = Block::arrayToBlock($lastReceived);
        $lastBlock = $this->getLatestBlock();
        if ($lastBlockReceived->getIndex() > $lastBlock->getIndex()) {
            if ($lastBlock->getHash() === $lastBlockReceived->getPreviousHash()) {
                if ($this->addBlock($lastBlockReceived)) {
                    Logger::log("blockchain", __FUNCTION__." Added a block");
                    $this->broadcast(array(
                        "type" => self::BLOCK_CHAIN,
                        "data" => array($this->getLatestBlock()->toArray())
                    ));
                }
            } elseif (count($blocks) === 1) {
                Logger::log("blockchain", __FUNCTION__." Asking blockchains to peers");
                $this->broadcast(array(
                    "type" => self::ALL_BLOCKS,
                    "data" => "ASKING_FOR_CHAINS"
                ));
            } else {
                Logger::log("blockchain", __FUNCTION__." Trying to replace chain");
                $chain = [];
                foreach ($blocks as $block) {
                    $chain[] = Block::arrayToBlock($block);
                }
                $this->replaceChain($chain);
            }
        } else {
            Logger::log("blockchain", __FUNCTION__." Received short chain");
        }
    }


    public function __construct(int $port)
    {
        Logger::log("blockchain", __FUNCTION__);
        $this->peers = [];
        $this->port = $port;
        $this->genesisBlock = new Block(0, '', '', 0, array("message"=>"This is the first block!"), 0, 0);
        $this->blocks = [$this->genesisBlock];
        $this->run();
    }

    private function broadcast($data)
    {
        Logger::log("blockchain", __FUNCTION__);
        foreach ($this->peers as $peer) {
            $resp = json_encode($data).self::DELIMITER;
            $peer->write($resp);
        }
    }

    private function run()
    {
        Logger::log("blockchain", __FUNCTION__);
        $port = $this->port;
        $blockchain = $this;

        Loop::run(static function () use ($blockchain, $port) {
            $peerHandler = function (ResourceSocket $socket) use (&$peerHandler, $blockchain) {
                Logger::log('p2p', $socket->getRemoteAddress().' peerHandler started!');
                $received = "";
                while (null !== $read = yield $socket->read()) {
                    $received .= $read;
                    Logger::log('p2p', $socket->getRemoteAddress().' wrote '.strlen($read).' bytes!');
                    if (strpos($received, self::DELIMITER) !== false) {
                        $message = json_decode($received, true);
                        $received = "";
                        if (isset($message["type"]) && isset($message["data"])) {
                            if ($message["type"] == self::LATEST_BLOCK) {
                                Logger::log('p2p', $socket->getRemoteAddress().' has requested the last block!');
                                $response = array(
                                    "type" => self::BLOCK_CHAIN,
                                    "data" => array($blockchain->getLatestBlock()->toArray())
                                );
                                $socket->write(json_encode($response).self::DELIMITER);
                            } elseif ($message["type"] == self::ALL_BLOCKS) {
                                Logger::log('p2p', $socket->getRemoteAddress().' has requested the entire blockchain!');
                                $response = array(
                                    "type" => self::BLOCK_CHAIN,
                                    "data" => $blockchain->getBlocksAsArray()
                                );
                                $resp = json_encode($response).self::DELIMITER;
                                $socket->write($resp);
                            } elseif ($message["type"] == self::BLOCK_CHAIN) {
                                Logger::log('p2p', $socket->getRemoteAddress().' has sent his chain!');
                                $blocks = $message["data"];
                                $blockchain->handleBlockchain($blocks);
                            } else {
                                Logger::log('p2p', $socket->getRemoteAddress().' has sent an unknown command!');
                            }
                        } else {
                            Logger::log('p2p', $socket->getRemoteAddress().' has sent a malformed request!');
                            $socket->write("malformed request".self::DELIMITER);
                        }
                    }
                }
                Logger::log('p2p', $socket->getRemoteAddress().' Peer disconnected!');
            };

            $peerToPeer = function ($port) use ($blockchain, $peerHandler) {
                $server = Server::listen('127.0.0.1:'.$port);
        
                Logger::log('p2p', 'Listening on: '.$server->getAddress());

                while ($socket = yield $server->accept()) {
                    Logger::log('p2p', $socket->getRemoteAddress().' Peer accepted!');
                    asyncCall($peerHandler, $socket);
                }
            };

            $controlServer = function ($port) use ($blockchain, $peerHandler) {
                $port += 1;
                $server = Server::listen('127.0.0.1:'.$port);
        
                while (true) {
                    Logger::log('control', 'Listening on: '.$server->getAddress());
                    $socket = yield $server->accept();
                    Logger::log('control', $socket->getRemoteAddress().' Controller accepted & started reading!');
                    $received = "";
                    while (null !== $read = yield $socket->read()) {
                        $received .= $read;
                        Logger::log('control', $socket->getRemoteAddress().' wrote '.strlen($read).' bytes!');
                        if (strpos($received, self::DELIMITER) !== false) {
                            $message = json_decode($received, true);
                            $received = "";
                            if (isset($message["type"]) && isset($message["data"])) {
                                if ($message["type"] == self::ADD_PEER) {
                                    Logger::log('control', $socket->getRemoteAddress().' has requested to connect to the peer: '.$message["data"].'!');
                                    $peer = yield connect($message["data"]);
                                    Logger::log('p2p', 'Connected to peer '.$message["data"]);
                                    $blockchain->peers[] = $peer;
                                    asyncCall($peerHandler, $peer);
                                } elseif ($message["type"] == self::MINE) {
                                    Logger::log('control', $socket->getRemoteAddress().' has requested to mine a block!');
                                    $blockchain->generateNextBlock($message["data"]);
                                } elseif ($message["type"] == self::GET_LAST_BLOCK) {
                                    Logger::log('control', $socket->getRemoteAddress().' has requested the last block!');
                                    $socket->write($blockchain->getLatestBlock()->toJson().self::DELIMITER);
                                } elseif ($message["type"] == self::GET_BLOCKCHAIN) {
                                    Logger::log('control', $socket->getRemoteAddress().' has requested the entire blockchain!');
                                    $socket->write(json_encode($blockchain->getBlocksAsArray()).self::DELIMITER);
                                } elseif ($message["type"] == self::GET_INFO) {
                                    Logger::log('control', $socket->getRemoteAddress().' has requested info of the blockchain!');
                                    $info = array(
                                        "difficulty" => $blockchain->getDifficulty($blockchain->getBlocks()),
                                        "peers" => $blockchain->getPeers()
                                    );
                                    $socket->write(json_encode($info).self::DELIMITER);
                                } else {
                                    Logger::log('control', $socket->getRemoteAddress().' has sent an unknown command!');
                                    $socket->write("unknown command".self::DELIMITER);
                                }
                            } else {
                                Logger::log('control', $socket->getRemoteAddress().' has sent a malformed request!');
                                $socket->write("malformed request".self::DELIMITER);
                            }
                        }
                    }
                    Logger::log('control', $socket->getRemoteAddress().' Controller disconnected!');
                }
            };
            
            asyncCall($controlServer, $port);
            asyncCall($peerToPeer, $port);
        });
    }
}
$blockchain = new Blockchain((int) $argv[1]);
