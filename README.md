# background-queue-rabbitmq

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

console:
	commands:
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
