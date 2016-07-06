<?php

namespace HelloFresh\Engine\Serializer\Adapter;

use HelloFresh\Engine\Serializer\SerializerInterface;

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
