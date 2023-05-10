<?php

namespace ADT\BackgroundQueueRabbitMQ;

use ADT\BackgroundQueue\BackgroundQueue;
use Exception;
use Kdyby\RabbitMq\Connection;
use PhpAmqpLib\Message\AMQPMessage;
use Tracy\Debugger;
use Tracy\ILogger;

class BackgroundQueueRabbitMQ
{
	const NOOP = 'noop';

	const MIN_EXECUTION_TIME = 1;

	const PRODUCER_GENERAL = 'general';

	private Connection $connection;

	private BackgroundQueue $backgroundQueue;

	public function __construct(Connection $connection, BackgroundQueue $backgroundQueue)
	{
		$this->connection = $connection;
		$this->backgroundQueue = $backgroundQueue;
	}

	public function publish(int $id, ?string $producer = null): void
	{
		$this->connection->getProducer($producer ?: self::PRODUCER_GENERAL)->publish($id);
	}

	public function publishNoop(): void
	{
		try {
			$this->connection->getProducer(self::PRODUCER_GENERAL)->publish(self::NOOP);
		} catch (Exception $e) {
			Debugger::log($e, ILogger::EXCEPTION);
		}
	}

	/**
	 * @throws Exception
	 */
	public function process(AMQPMessage $message): bool
	{
		$executionTime = -microtime(true);

		$body = $message->getBody();

		if ($body === self::NOOP) {
			return true;
		}

		$this->backgroundQueue->process((int) $body);

		/**
		 * Jedno zpracování je případně uměle protaženo sleepem, aby si *supervisord*
		 * nemyslel, že se proces ukončil moc rychle.
		 */
		$executionTime += microtime(true);
		if ($executionTime < self::MIN_EXECUTION_TIME) {
			// Pokud bychom zpracovali řádek z fronty moc rychle, udělej sleep
			usleep((int) ((self::MIN_EXECUTION_TIME - $executionTime) * 1000 * 1000));
		}

		// vždy označit zprávu jako provedenou (smazat ji z rabbit DB)
		return true;
	}
}
