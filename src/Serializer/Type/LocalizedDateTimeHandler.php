<?php

namespace HelloFresh\Engine\Serializer\Type;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Context;

class LocalizedDateTimeHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'LocalizedDateTime',
                'method' => 'serializeLocalizedDateTimeToJson',
            ),
        );
    }

    public function serializeLocalizedDateTimeToJson(JsonSerializationVisitor $visitor, \DateTime $date, array $type, Context $context)
    {
        $df = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);

        return $df->format($date);
    }
}
