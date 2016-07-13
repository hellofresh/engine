<?php

namespace HelloFresh\Engine\Serializer\Type;

use Collections\Vector;
use Collections\VectorInterface;
use JMS\Serializer\Context;
use JMS\Serializer\GenericDeserializationVisitor;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;

class VectorHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $formats = ['json', 'xml', 'yml'];
        $collectionTypes = [
            'Vector',
            Vector::class,
        ];

        $methods = [];
        foreach ($collectionTypes as $type) {
            foreach ($formats as $format) {
                $methods[] = [
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serializeCollection',
                ];

                $methods[] = [
                    'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserializeCollection',
                ];
            }
        }

        return $methods;
    }

    public function serializeCollection(
        VisitorInterface $visitor,
        VectorInterface $collection,
        array $type,
        Context $context
    ) {
        // We change the base type, and pass through possible parameters.
        $type['name'] = 'array';

        return $visitor->visitArray($collection->toArray(), $type, $context);
    }

    public function deserializeCollection(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        // See above.
        $type['name'] = 'array';

        // When there is not root set for the visitor we need to handle the vector result setting
        // manually this is related to https://github.com/schmittjoh/serializer/issues/95
        $isRoot = null === $visitor->getResult();
        if ($isRoot && $visitor instanceof GenericDeserializationVisitor) {
            $metadata = new ClassMetadata(Vector::class);
            $vector = new Vector();

            $visitor->startVisitingObject($metadata, $vector, $type, $context);

            $array = $visitor->visitArray($data, $type, $context);
            $vector->setAll($array);

            $visitor->endVisitingObject($metadata, $vector, $type, $context);

            return $vector;
        }

        // No a root so just return the vector
        return new Vector($visitor->visitArray($data, $type, $context));
    }
}
