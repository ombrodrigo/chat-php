<?php  

require_once 'vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;
use Server\ConnectionPool;

$loop = Factory::create();
$server = new Server('0.0.0.0:1234', $loop);
$connectionPool = new ConnectionPool(); 

$server->on('connection', function (ConnectionInterface $connection) use ($connectionPool) {
	$connectionPool->add($connection);
});

$loop->run();