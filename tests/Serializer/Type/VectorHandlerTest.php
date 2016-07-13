<?php

namespace HelloFresh\Tests\Engine\Serializer\Type;

use Collections\Vector;
use HelloFresh\Engine\Serializer\Type\VectorHandler;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializerBuilder;

class VectorHandlerTest extends JMSSerializerHandlerTestCase
{
    /**
     * @dataProvider providerTypes
     * @param string $type
     */
    public function testJsonSerializationAndDeserializationRootLevel($type)
    {
        $expectedVector = new Vector(['foo', 'bar']);
        $expectedJson = '["foo", "bar"]';

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $this->serializer->serialize($expectedVector, 'json')
        );

        $this->assertEquals(
            $expectedVector,
            $this->serializer->deserialize($expectedJson, $type, 'json')
        );
    }

    /**
     * @dataProvider providerTypes
     * @param string $type
     */
    public function testJsonSerializationAndDeserializationChildLevel($type)
    {
        $expectedVector = [ 'details' => new Vector(['foo', 'bar']) ];
        $expectedJson = '{ "details": ["foo", "bar"] }';

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $this->serializer->serialize($expectedVector, 'json')
        );

        $this->assertEquals(
            $expectedVector,
            $this->serializer->deserialize($expectedJson, sprintf('array<string,%s>', $type), 'json')
        );
    }

    public function providerTypes()
    {
        return [
            ['Vector'],
            [Vector::class],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function configureBuilder(SerializerBuilder $builder)
    {
        $builder->configureHandlers(function (HandlerRegistryInterface $registry) {
            $registry->registerSubscribingHandler(new VectorHandler());
        });
    }
}
