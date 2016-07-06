<?php

namespace HelloFresh\Engine\Serializer\Type;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;
use Money\Currency;
use Money\Money;

class MoneyHandler implements SubscribingHandlerInterface
{
    const TYPE_MONEY = 'money';

    /**
     * @return string[][]
     */
    public static function getSubscribingMethods()
    {
        $formats = [
            'json',
            'xml',
            'yml',
        ];
        $methods = [];
        foreach ($formats as $format) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'type' => self::TYPE_MONEY,
                'format' => $format,
                'method' => 'serializeMoney',
            ];
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => self::TYPE_MONEY,
                'format' => $format,
                'method' => 'deserializeMoney',
            ];
        }

        return $methods;
    }

    /**
     * @param \JMS\Serializer\VisitorInterface $visitor
     * @param mixed $data
     * @param mixed[] $type
     * @param \JMS\Serializer\Context $context
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function deserializeMoney(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        $parts = explode(' ', $data);

        return new Money((int)$parts[0], new Currency($parts[1]));
    }

    /**
     * @param \JMS\Serializer\VisitorInterface $visitor
     * @param Money $money
     * @param mixed[] $type
     * @param \JMS\Serializer\Context $context
     * @return string
     */
    public function serializeMoney(VisitorInterface $visitor, Money $money, array $type, Context $context)
    {
        return (string)$money->getAmount() . ' ' . $money->getCurrency()->getName();
    }
}
