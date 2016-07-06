<?php

namespace HelloFresh\Engine\Serializer\Type;

use Collections\Vector;
use Collections\VectorInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

class VectorHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'Vector',
                'method' => 'serializeCollection'
            ],
            [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'Vector',
                'method' => 'deserializeCollection'
            ]
        ];
    }

    public function serializeCollection(
        VisitorInterface $visitor,
        VectorInterface $collection,
        array $type,
        Context $context
    ) {
        // We change the base type, and pass through possible parameters.
        $type['name'] = 'array';

        return $visitor->visitArray($collection->toArray(), $type, $context);
    }

    public function deserializeCollection(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        // See above.
        $type['name'] = 'array';

        return new Vector($visitor->visitArray($data, $type, $context));
    }
}
