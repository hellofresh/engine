<?php

namespace HelloFresh\Engine\Serializer\Adapter;

use HelloFresh\Engine\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as JmsSerializerInterface;

class PhpJsonSerializerAdapter implements SerializerInterface
{
    /**
     * @inheritdoc
     */
    public function serialize($data, $format, $groups = null)
    {
        return json_encode($data);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($data, $type, $format, $groups = null)
    {
        return json_decode($data);
    }
}
