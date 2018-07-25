<?php

namespace Server;

use React\Socket\ConnectionInterface;
use React\EventLoop\LoopInterface;
use SplObjectStorage;

class ConnectionPool
{
	private $connections;

	public function __construct()
	{
		$this->connections = new SplObjectStorage();
	}

	public function add(ConnectionInterface $connection)
	{
		$this->listner($connection);
	}

	public function listner(ConnectionInterface $connection)
	{
		$this->connections->attach($connection);

		$this->setConnectionName($connection, '');

		$connection->write(PHP_EOL . 'Enter your name: ');
		
		$connection->on('data', function ($data) use ($connection) {
			$name = $this->getConnectioName($connection);

			if (!$name) {
				return $this->messageNewConnection($connection, $data);
			}

			$this->sendAll($connection, PHP_EOL . "* {$name}: {$data}");
		});

		$connection->on('close', function () use ($connection) {
			$this->stopConnection($connection);
		});
	}

	private function messageNewConnection(ConnectionInterface $connection, $name)
	{
		$name = trim($name);
		$this->setConnectionName($connection, $name);
		$connection->write(PHP_EOL . "Wellcome {$name} ...");
		$this->sendAll($connection, "User * {$name} joins the chat ...");
		return;
	}

	private function stopConnection(ConnectionInterface $connection)
	{
		$userName = $this->getConnectioName($connection);
		$this->connections->detach($connection);
		$this->sendAll($connection, "User * {$userName} exited.");
	}

	private function sendAll(ConnectionInterface $except, $message)
	{
		if (!trim($message)) {
			return;
		}

		foreach ($this->connections as $conn) {
			if ($conn !== $except) {
				$conn->write(PHP_EOL . $message);
			}
		}
	}

	private function setConnectionName(ConnectionInterface $connection, $name)
	{
		$this->connections->offsetSet($connection, $name);
	}

	private function getConnectioName(ConnectionInterface $connection)
	{
		return $this->connections->offsetGet($connection);
	}
}
