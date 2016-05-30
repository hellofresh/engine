# Event Dispatcher Component

Event dispatcher component providing event dispatchers to your application.

The component provides an event dispatcher interface and a simple
implementation.

```php
$dispatcher = new EventDispatcher();
$dispatcher->addListener(SomeEvent::class, function(SomeEvent $event){
    echo $event->greetings()
});
```
