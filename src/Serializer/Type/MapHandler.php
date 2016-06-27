<?php

namespace HelloFresh\Engine\Serializer\Type;

use Collections\Map;
use Collections\Iterable;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

class MapHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Map',
                'method' => 'serializeCollection'
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'Map',
                'method' => 'deserializeCollection'
            ]
        ];

    }

    public function serializeCollection(VisitorInterface $visitor, Iterable $collection, array $type, Context $context)
    {
        // We change the base type, and pass through possible parameters.
        $type['name'] = 'array';

        return $visitor->visitArray($collection->toArray(), $type, $context);
    }

    public function deserializeCollection(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        // See above.
        $type['name'] = 'array';

        return new Map($data, $type, $context);
    }
}
