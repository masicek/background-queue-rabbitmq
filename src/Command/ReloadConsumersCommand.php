<?php

namespace ADT\BackgroundQueue\Command;

use ADT\BackgroundQueue\BackgroundQueueRabbitMQ;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadConsumersCommand extends Command
{
	protected static $defaultName = 'background-queue:reload-consumers';

	protected function configure()
	{
		$this->addArgument(
			"number",
			InputArgument::OPTIONAL,
			'Number of consumers to reload.'
		);
		$this->setDescription('Creates the specified number of noop messages to reload consumers.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		/** @var BackgroundQueueRabbitMQ $rabbitMQ */
		$rabbitMQ = $this->getHelper("container")->getByType(BackgroundQueueRabbitMQ::class);

		for ($i = 0; $i < $input->getArgument("number"); $i++) {
			$rabbitMQ->publishNoop();
		}
	}
}
