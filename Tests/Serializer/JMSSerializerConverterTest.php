<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use JMS\Serializer\Annotation as XML;
use SimpleThings\FormSerializerBundle\Serializer\JMSSerializerConverter;
use SimpleThings\FormSerializerBundle\Tests\TestCase;

class JMSSerializerConverterTest extends TestCase
{
    /**
     * @var JMSSerializerConverter
     */
    private $converter;

    public function setUp()
    {
        $this->converter = new JMSSerializerConverter($this->createJmsSerializer()->getMetadataFactory());
    }

    public function testConverter()
    {
        $code = $this->converter->generateFormPHpCode(__NAMESPACE__ . '\Fixture\Object');

        $this->assertEquals(file_get_contents(__DIR__ . '/Fixture/ObjectType.php'), $code);
    }

    public function testConverterWithTypes()
    {
        $code = $this->converter->generateFormPHpCode(__NAMESPACE__ . '\Fixture\Foo');

        $this->assertEquals(file_get_contents(__DIR__ . '/Fixture/FooType.php'), $code);
    }

    public function testConverterCollectionTypes()
    {
        $code = $this->converter->generateFormPHpCode(__NAMESPACE__ . '\Fixture\Bar');

        $this->assertEquals(file_get_contents(__DIR__ . '/Fixture/BarType.php'), $code);
    }
}
