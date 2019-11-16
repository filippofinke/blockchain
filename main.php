<?php
use FilippoFinke\Blockchain;
require __DIR__ . '/vendor/autoload.php';

/**
 * A simple example.
 */

$firstBlockChain = new Blockchain();
$secondBlockChain = new Blockchain();

echo "First Blockchain size: ".count($firstBlockChain->getBlocks()).PHP_EOL;
echo "Second Blockchain size: ".count($secondBlockChain->getBlocks()).PHP_EOL;
echo "Adding 3 block to the first blockchain".PHP_EOL;
$data = array(
    "message" => "test"
);
for ($i = 0; $i < 3; $i++) {
    $blockAdded = $firstBlockChain->generateNextBlock($data);
    echo "Added ".$blockAdded->getHash()." to the first blockchain".PHP_EOL;
}
echo "First Blockchain size: ".count($firstBlockChain->getBlocks()).PHP_EOL;
echo "Second Blockchain size: ".count($secondBlockChain->getBlocks()).PHP_EOL;
echo "Trying to replace first blockchain with the second one".PHP_EOL;
$secondChain = $secondBlockChain->getBlocks();
$status = $firstBlockChain->replaceChain($secondChain);
$status = ($status)?'SUCCESS':'FAILED';
echo "Status of the swap between the second and the first one: ".$status.PHP_EOL;
echo "Trying to replace second blockchain with the first one".PHP_EOL;
$firstChain = $firstBlockChain->getBlocks();
$status = $secondBlockChain->replaceChain($firstChain);
$status = ($status)?'SUCCESS':'FAILED';
echo "Status of the swap between the second and the first one: ".$status.PHP_EOL;
echo "First Blockchain size: ".count($firstBlockChain->getBlocks()).PHP_EOL;
echo "Second Blockchain size: ".count($secondBlockChain->getBlocks()).PHP_EOL;
echo "First Blockchain last block:".PHP_EOL.$firstBlockChain->getLatestBlock().PHP_EOL;
echo "Second Blockchain last block:".PHP_EOL.$secondBlockChain->getLatestBlock().PHP_EOL;
