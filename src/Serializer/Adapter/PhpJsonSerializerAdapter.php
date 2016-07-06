<?php

namespace HelloFresh\Engine\Serializer\Adapter;

use HelloFresh\Engine\Serializer\SerializerInterface;

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
