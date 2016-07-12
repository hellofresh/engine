<?php

namespace HelloFresh\Tests\Engine\Serializer\Type;

use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;
use JMS\Serializer\Serializer;

abstract class JMSSerializerHandlerTestCase extends TestCase
{
    /**
     * @var Serializer
     */
    protected $serializer;

    protected function setUp()
    {
        parent::setUp();
        
        $this->serializer = $this->createSerializer();
    }

    /**
     * Create a serializer instance.
     *
     * @return Serializer
     */
    protected function createSerializer()
    {
        $builder = new SerializerBuilder();
        $builder->addDefaultHandlers();
        $builder->addDefaultDeserializationVisitors();
        $builder->addDefaultSerializationVisitors();
        
        $this->configureBuilder($builder);
        
        return $builder->build();
    }

    /**
     * Configure the serializer builder for the test case.
     *
     * @param SerializerBuilder $builder
     * @return void
     */
    abstract protected function configureBuilder(SerializerBuilder $builder);
}
