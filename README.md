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
