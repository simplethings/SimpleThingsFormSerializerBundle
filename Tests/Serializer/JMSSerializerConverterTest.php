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
        $options->setDefaults(array(
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

        echo $code;
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
