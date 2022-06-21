# background-queue-rabbitmq

```neon
parameters:
	rabbitMQ:
		name: YOUR_NAME

extensions:
	rabbitMQ: Kdyby\RabbitMq\DI\RabbitMqExtension

services:
	rabbitMQ: ADT\BackgroundQueueRabbitMQ\BackgroundQueueRabbitMQ

rabbitMQ:
	connection:
		host: rabbitmq
		user: viaphone
		password: draughts-mounting-barnyard-nestle

	producers:
		general:
			exchange: {name: %rabbitMQ.name%, type: direct}
			queue: {name: %rabbitMQ.name%, arguments: {'x-queue-type': ['S', 'quorum']}}
			contentType: text/plain

	consumers:
		general:
			exchange: {name: %rabbitMQ.name%, type: direct}
			queue: {name: %rabbitMQ.name%, arguments: {'x-queue-type': ['S', 'quorum']}}
			callback: [@rabbitMQ, 'process']
			qos:
				# Consumery máme nastavené tak, že zpracují max 1 zprávu, takže nastavíme,
				# aby si jich více nezabíral.
				prefetchCount: 1
```