# EventBus Component

Provides event bus and event listeners abstractions.

The component provides interfaces for an event bus and event listeners, but also an implementation of a 
simple event bus and an event bus that will record published events (useful for testing).

```php
$eventBus = new SimpleEventBus();
$eventBus->subscribe(new ExampleListener());

$eventBus->publish(new SomeEvent());
```
