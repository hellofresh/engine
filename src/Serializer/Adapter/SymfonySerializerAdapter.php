<?php

namespace HelloFresh\Engine\Serializer\Adapter;

use HelloFresh\Engine\Serializer\SerializerInterface;
use JMS\Serializer\SerializerInterface as JmsSerializerInterface;
use Symfony\Component\Serializer\Serializer;

class SymfonySerializerAdapter implements SerializerInterface
{
    /**
     * @var JmsSerializerInterface
     */
    protected $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function serialize($data, $format, $groups = null)
    {
        return $this->serializer->serialize($data, $format, $groups);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($data, $type, $format, $groups = null)
    {
        return $this->serializer->deserialize($data, $type, $format, $groups);
    }
}
