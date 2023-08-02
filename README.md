# Background Queue for Nette and RabbitMQ

### 1.1 Installation

```
composer require adt/background-queue-nette
```

### 1.2 Configuration

```neon
extensions:
	backgroundQueue: ADT\BackgroundQueue\DI\BackgroundQueueExtension

backgroundQueue:
	callbacks:
		sendEmail: [@App\Model\Mailer, sendEmail]
		...
	notifyOnNumberOfAttempts: 5 # počet pokusů o zpracování záznamu před zalogováním
	tempDir: %tempDir% # cesta pro uložení zámku proti vícenásobnému spuštění commandu
	queue: general # nepovinné, název fronty, do které se ukládají a ze které se vybírají záznamy
	connection: %database% # parametry predavane do Doctrine\Dbal\Connection
	amqpPublishCallback: [@rabbitMq, 'publish'] # nepovinné, callback, který publishne zprávu do brokera
	amqpWaitingQueueName: 'waiting' # nepovinné, název queue, kam ukládat záznamy, které ještě nelze zpracovat
```

```neon
parameters:
	rabbitMQ:
		host: HOST
		user: USER
		password: PASSWORD
		name: NAME

extensions:
	rabbitMQ: Kdyby\RabbitMq\DI\RabbitMqExtension

services:
	rabbitMQ: ADT\BackgroundQueue\BackgroundQueueRabbitMQ
	
	- ADT\BackgroundQueue\Command\ReloadConsumersCommand

	rabbitMQ.console.0:
		class: Kdyby\RabbitMq\Command\ConsumerCommand
		tags: [console.command: rabbitmq:consumer]
	rabbitMQ.console.1:
		class: Kdyby\RabbitMq\Command\PurgeConsumerCommand
		tags: [console.command: rabbitmq:purge]
	rabbitMQ.console.2:
		class: Kdyby\RabbitMq\Command\RpcServerCommand
		tags: [console.command: rabbitmq:rpc-server]
	rabbitMQ.console.3:
		class: Kdyby\RabbitMq\Command\SetupFabricCommand
		tags: [console.command: rabbitmq:setup-fabric]
	rabbitMQ.console.4:
		class: Kdyby\RabbitMq\Command\StdInProducerCommand
		tags: [console.command: rabbitmq:stdin-producer]

rabbitMQ:
	connection:
		host: %rabbitMQ.host%
		user: %rabbitMQ.user%
		password: %rabbitMQ.password%

	producers:
		general:
			exchange: {name: %rabbitMQ.name%, type: direct}
			queue: {name: %rabbitMQ.name%, arguments: {'x-queue-type': ['S', 'quorum']}}
			contentType: text/plain

		waiting:
			exchange: {name: %rabbitMQ.name%_waiting, type: direct}
			queue: {name: %rabbitMQ.name%_waiting, arguments: {'x-dead-letter-exchange': ['S', %rabbitMQ.name%], 'x-message-ttl': ['I', 100]}} # 100ms
			contentType: text/plain

	consumers:
		general:
			exchange: {name: %rabbitMQ.name%, type: direct}
			queue: {name: %rabbitMQ.name%, arguments: {'x-queue-type': ['S', 'quorum']}}
			callback: [@rabbitMQ, 'process']
			qos:
				# Consumers consume only 1 message and are restarted
				# We need to reflect this by setting prefetchCount to 1
				prefetchCount: 1
```
