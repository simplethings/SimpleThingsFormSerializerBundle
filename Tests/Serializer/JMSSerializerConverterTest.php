<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use SimpleThings\FormSerializerBundle\Serializer\JMSSerializerConverter;
use JMS\SerializerBundle\Annotation as XML;

class JMSSerializerConverterTest extends \PHPUnit_Framework_TestCase
{
    private $converter;

    public function setUp()
    {
        $fileLocator = new \Metadata\Driver\FileLocator(array());
        $driver      = new \Metadata\Driver\DriverChain(array(
            new \JMS\SerializerBundle\Metadata\Driver\YamlDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\XmlDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\PhpDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\AnnotationDriver(new \Doctrine\Common\Annotations\AnnotationReader())
        ));

        $this->converter = new JMSSerializerConverter(new \Metadata\MetadataFactory($driver));
    }

    public function testConverter()
    {
        $code = $this->converter->generateFormPHpCode(__NAMESPACE__ . "\\Object");

        $this->assertEquals(<<<'PHP'
<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('foo', 'text')
            ->add('bar', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleThings\FormSerializerBundle\Tests\Serializer\Object'
        ));
    }

    public function getName()
    {
        return 'object';
    }
}

PHP
        , $code);
    }

    public function testConverterWithTypes()
    {
        $code = $this->converter->generateFormPHpCode(__NAMESPACE__ . "\\Foo");

        $this->assertEquals(<<<'PHP'
<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('object', new ObjectType())
            ->add('int', 'integer')
            ->add('date', 'datetime')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleThings\FormSerializerBundle\Tests\Serializer\Foo'
        ));
    }

    public function getName()
    {
        return 'foo';
    }
}

PHP
        , $code);
    }

    public function testConverterCollectionTypes()
    {
        $code = $this->converter->generateFormPHpCode(__NAMESPACE__ . "\\Bar");

        $this->assertEquals(<<<'PHP'
<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objects', 'collection', array('type' => new ObjectType(), 'serialize_xml_inline' => false, 'serialize_xml_name' => 'object'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleThings\FormSerializerBundle\Tests\Serializer\Bar'
        ));
    }

    public function getName()
    {
        return 'bar';
    }
}

PHP
        , $code);
    }
}

/**
 * @XML\XmlRoot("test")
 */
class Object
{
    public $foo;
    public $bar;
}

/**
 * @XML\XmlRoot("foo")
 */
class Foo
{
    /**
     * @XML\Type("SimpleThings\FormSerializerBundle\Tests\Serializer\Object")
     */
    public $object;

    /**
     * @XML\Type("integer")
     */
    public $int;

    /**
     * @XML\Type("DateTime")
     */
    public $date;
}

/**
 * @XML\XmlRoot("bar")
 */
class Bar
{
    /**
     * @XML\Type("array<SimpleThings\FormSerializerBundle\Tests\Serializer\Object>")
     * @XML\XmlList(entry="object", inline=false)
     */
    public $objects;
}

