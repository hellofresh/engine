# CommandBus Component

Primitives to use commands in your application.

## Command bus

An interface and two simple implementations of a command bus where commands can
be dispatched on.

## Command handler

An interface and convenient base class that command handlers can extend.

The base class provided by this component uses a convention to find out whether
the command handler can execute a command or not. To signal that your command
handler can handle a command `ExampleCommand`, just implement the
`handle` method.

```php
$commandBus = new SimpleCommandBus();
$commandBus->subscribe(ExampleCommand::class, new ExampleHandler());

$command = new ExampleCommand('hello world');
$commandBus->execute($command);
```

## Testing

A helper to implement scenario based tests for command handlers that use an
event store.

