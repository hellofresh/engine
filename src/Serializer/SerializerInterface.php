<?php

namespace HelloFresh\Engine\Serializer;

interface SerializerInterface
{
    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array|scalar $data
     * @param string $format
     * @param null $groups
     * @return string
     *
     */
    public function serialize($data, $format, $groups = null);

    /**
     * Deserializes the given data to the specified type.
     *
     * @param string $data
     * @param string $type
     * @param string $format
     * @param null $groups
     * @return array|scalar|object
     *
     */
    public function deserialize($data, $type, $format, $groups = null);
}
