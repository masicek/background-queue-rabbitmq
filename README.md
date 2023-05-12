# Background Queue RabbitMQ

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
	rabbitMQ: ADT\BackgroundQueueRabbitMQ\BackgroundQueueRabbitMQ
	
	- ADT\BackgroundQueueRabbitMQ\Command\ReloadConsumersCommand

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
			queue: {name: %rabbitMQ.name%_waiting, arguments: {'x-dead-letter-exchange': ['S', %rabbitMQ.name%], 'x-message-ttl': ['I', 1000]}} # 1s
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
