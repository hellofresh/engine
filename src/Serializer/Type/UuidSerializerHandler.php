<?php

namespace HelloFresh\Engine\Serializer\Type;

use HelloFresh\Engine\Serializer\Exception\DeserializationInvalidValueException;
use HelloFresh\Engine\Serializer\Exception\InvalidUuidException;
use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\VisitorInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidSerializerHandler implements \JMS\Serializer\Handler\SubscribingHandlerInterface
{
    const PATH_FIELD_SEPARATOR = '.';
    const TYPE_UUID = 'uuid';

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
                'type' => self::TYPE_UUID,
                'format' => $format,
                'method' => 'serializeUuid',
            ];
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'type' => self::TYPE_UUID,
                'format' => $format,
                'method' => 'deserializeUuid',
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
    public function deserializeUuid(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        try {
            return $this->deserializeUuidValue($data);
        } catch (InvalidUuidException $e) {
            throw new DeserializationInvalidValueException(
                $this->getFieldPath($visitor, $context),
                $e
            );
        }
    }

    /**
     * @param string $uuidString
     * @return \Ramsey\Uuid\UuidInterface
     */
    private function deserializeUuidValue($uuidString)
    {
        if (!Uuid::isValid($uuidString)) {
            throw new InvalidUuidException($uuidString);
        }

        return Uuid::fromString($uuidString);
    }

    /**
     * @param \JMS\Serializer\VisitorInterface $visitor
     * @param \Ramsey\Uuid\UuidInterface $uuid
     * @param mixed[] $type
     * @param \JMS\Serializer\Context $context
     * @return string
     */
    public function serializeUuid(VisitorInterface $visitor, UuidInterface $uuid, array $type, Context $context)
    {
        return $uuid->toString();
    }

    /**
     * @param \JMS\Serializer\VisitorInterface $visitor
     * @param \JMS\Serializer\Context $context
     * @return string
     */
    private function getFieldPath(VisitorInterface $visitor, Context $context)
    {
        $path = '';
        foreach ($context->getMetadataStack() as $element) {
            if ($element instanceof PropertyMetadata) {
                $name = ($element->serializedName !== null) ? $element->serializedName : $element->name;
                if ($visitor instanceof AbstractVisitor) {
                    $name = $visitor->getNamingStrategy()->translateName($element);
                }
                $path = $name . self::PATH_FIELD_SEPARATOR . $path;
            }
        }
        $path = rtrim($path, self::PATH_FIELD_SEPARATOR);

        return $path;
    }
}
