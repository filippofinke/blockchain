<?php
define("DELIMITER", "\r\n");

if (!isset($argv[1])) {
    exit("Provide a node address!".PHP_EOL);
}
$node = $argv[1];
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
[$ip, $port] = explode(":", $node);
try {
    socket_connect($socket, $ip, $port);
} catch (Exception $e) {
    echo "Connection refused from $node".PHP_EOL;
}
$running = true;
$result = null;
while ($running) {
    system('clear');
    echo "Connected to $node".PHP_EOL;
    $request = array("type" => 7,"data" => "");
    socket_write($socket, json_encode($request).DELIMITER);
    $response = json_decode(read($socket), true);
    echo "Difficulty: ".$response["difficulty"].PHP_EOL;
    echo "Peers:".PHP_EOL;
    foreach ($response["peers"] as $peer) {
        echo " - $peer".PHP_EOL;
    }
    echo PHP_EOL."Commands:".PHP_EOL;
    echo "0 - Mine a block".PHP_EOL;
    echo "1 - Add a peer".PHP_EOL;
    echo "2 - Get last block".PHP_EOL;
    echo "3 - Get blockchain".PHP_EOL;
    if ($result) {
        echo PHP_EOL."- Result -".PHP_EOL;
        echo $result.PHP_EOL;
    }
    $command = readline("Insert a command: ");
    if ($command == "0") {
        $request = array(
            "type" => 3,
            "data" => array(
                "message" => readline("Insert a message: ")
            )
        );
        socket_write($socket, json_encode($request).DELIMITER);
        $result = "Requested to mine a block!";
    } elseif ($command == "1") {
        $request = array(
            "type" => 4,
            "data" => readline("Insert a peer: ")
        );
        socket_write($socket, json_encode($request).DELIMITER);
        $result = "Requested to add a peer!";
    } elseif ($command == "2") {
        $request = array("type" => 5,"data" => "");
        socket_write($socket, json_encode($request).DELIMITER);
        $result = read($socket);
    } elseif ($command == "3") {
        $request = array("type" => 6,"data" => "");
        socket_write($socket, json_encode($request).DELIMITER);
        $result = read($socket);
    } else {
        $result = "Invalid command".PHP_EOL;
    }
}
socket_close($socket);

function read($socket)
{
    $read = "";
    while (true) {
        $read .= socket_read($socket, 1024);
        if (strpos($read, DELIMITER) !== false) {
            return $read;
        }
    }
}
