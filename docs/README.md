## Table of contents

- [\FilippoFinke\Block](#class-filippofinkeblock)
- [\FilippoFinke\Blockchain](#class-filippofinkeblockchain)

<hr />

### Class: \FilippoFinke\Block

> Class that represent a single block of the blockchain.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>int</em> <strong>$index</strong>, <em>string</em> <strong>$hash</strong>, <em>string</em> <strong>$previousHash</strong>, <em>int</em> <strong>$timestamp</strong>, <em>array</em> <strong>$data</strong>)</strong> : <em>void</em><br /><em>Block constructor.</em> |
| public | <strong>__toString()</strong> : <em>array The block as json string.</em><br /><em>Override of the __toString method.</em> |
| public static | <strong>calculateHash(</strong><em>int</em> <strong>$index</strong>, <em>string</em> <strong>$previousHash</strong>, <em>int</em> <strong>$timestamp</strong>, <em>array</em> <strong>$data</strong>)</strong> : <em>string A sha256 hash of the block.</em><br /><em>Method used to calculate the hash for a block.</em> |
| public static | <strong>calculateHashForBlock(</strong><em>[\FilippoFinke\Block](#class-filippofinkeblock)</em> <strong>$block</strong>)</strong> : <em>string A sha256 hash of the block.</em><br /><em>Method used to calculate the hash for a block.</em> |
| public | <strong>getData()</strong> : <em>array The data stored into the block.</em><br /><em>Getter method for the block data.</em> |
| public | <strong>getHash()</strong> : <em>string The hash of the block.</em><br /><em>Getter method for the block hash.</em> |
| public | <strong>getIndex()</strong> : <em>int The index of the block.</em><br /><em>Getter method for the block index.</em> |
| public | <strong>getPreviousHash()</strong> : <em>string The previous hash of the block.</em><br /><em>Getter method for the block previous hash.</em> |
| public | <strong>getTimestamp()</strong> : <em>int The creation timestamp of the block.</em><br /><em>Getter method for the block timestamp.</em> |
| public static | <strong>isValidStructure(</strong><em>mixed</em> <strong>$array</strong>)</strong> : <em>bool If the structure is valid or not.</em><br /><em>Method that validate the structure of an array.</em> |
| public static | <strong>jsonToBlock(</strong><em>string</em> <strong>$json</strong>)</strong> : <em>Block A block if the string has been converted otherwise null.</em><br /><em>Method used to convert a json string to a block.</em> |
| public | <strong>toArray()</strong> : <em>array The block as array.</em><br /><em>Method used to convert the block to an array.</em> |
| public | <strong>toJson()</strong> : <em>array The block as json string.</em><br /><em>Method used to convert the block to a json string.</em> |

<hr />

### Class: \FilippoFinke\Blockchain

> Class that represent the blockchain.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em><br /><em>Constructor method of the blockchain. It generates the genesis block.</em> |
| public | <strong>addBlock(</strong><em>[\FilippoFinke\Block](#class-filippofinkeblock)</em> <strong>$newBlock</strong>)</strong> : <em>bool True if the block has been added otherwise false.</em><br /><em>Method used to add a block to the chain.</em> |
| public | <strong>generateNextBlock(</strong><em>array</em> <strong>$data</strong>)</strong> : <em>Block The block generated or null.</em><br /><em>Method used to generate a new block.</em> |
| public | <strong>getBlocks()</strong> : <em>array The blocks of the blockchain.</em><br /><em>Getter method for the blockchain blocks.</em> |
| public | <strong>getGenesisBlock()</strong> : <em>Block The genesis block.</em><br /><em>Getter method for the genesis block.</em> |
| public | <strong>getLatestBlock()</strong> : <em>mixed</em><br /><em>Getter method for the latest block.</em> |
| public | <strong>isValidChain(</strong><em>array</em> <strong>$blocks</strong>)</strong> : <em>bool True if the chain is valid otherwise false.</em><br /><em>Method used to verify an entire chain.</em> |
| public | <strong>isValidGenesis(</strong><em>[\FilippoFinke\Block](#class-filippofinkeblock)</em> <strong>$genesisBlock</strong>)</strong> : <em>bool True if the block is valid otherwise false.</em><br /><em>Method used to verify the genesis block.</em> |
| public | <strong>isValidNewBlock(</strong><em>[\FilippoFinke\Block](#class-filippofinkeblock)</em> <strong>$newBlock</strong>, <em>[\FilippoFinke\Block](#class-filippofinkeblock)</em> <strong>$previousBlock</strong>)</strong> : <em>bool True if the block is valid otherwise false.</em><br /><em>Method used to verify if a new block is valid based on another block.</em> |
| public | <strong>replaceChain(</strong><em>array</em> <strong>$blocks</strong>)</strong> : <em>bool True if the chain has been replace otherwise false.</em><br /><em>Method used to replace the current chain with another one. If the new blockchain is valid and bigger than the current one it will replace the current one.</em> |

