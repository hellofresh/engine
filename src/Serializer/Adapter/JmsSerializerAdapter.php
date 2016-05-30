<?php

namespace HelloFresh\Engine\Serializer\Adapter;

use HelloFresh\Engine\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as JmsSerializerInterface;

class JmsSerializerAdapter implements SerializerInterface
{
    /**
     * @var JmsSerializerInterface
     */
    protected $serializer;

    public function __construct(JmsSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function serialize($data, $format, $groups = null)
    {
        $context = null;

        if ($groups) {
            $context = SerializationContext::create()->setGroups($groups);
        }

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($data, $type, $format, $groups = null)
    {
        $context = null;

        if ($groups) {
            $context = DeserializationContext::create()->setGroups($groups);
        }

        return $this->serializer->deserialize($data, $type, $format, $context);
    }
}
