<?php

namespace HelloFresh\Engine\Serializer\Adapter;

use HelloFresh\Engine\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as JmsSerializerInterface;

class PhpSerializerAdapter implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize($data, $format, $groups = null)
    {
        return serialize($data);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($data, $type, $format, $groups = null)
    {
        return unserialize($data);
    }
}
