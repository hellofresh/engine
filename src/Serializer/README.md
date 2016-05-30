# Event Store Component

Serializer component provides serializers to your application.

The component provides a simple serializer interface and a serializer implementation based on "handwritten" 
serializers.

The available adapters are:

* JMS Serializer
* Symfony Serializer
* PHP Serializer

## Usage

```php
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use HelloFresh\Engine\Serializer\Adapter\JmsSerializerAdapter;

$jmsSerializer = SerializerBuilder::create()
    ->setMetadataDirs(['' => __DIR__ . '/metadata'])
    ->configureHandlers(function (HandlerRegistry $registry) {
        $registry->registerSubscribingHandler(new ArrayListHandler());
        $registry->registerSubscribingHandler(new UuidSerializerHandler());
    })
    ->addDefaultHandlers()
    ->build();

$serializer = new JmsSerializerAdapter($jmsSerializer);
```
