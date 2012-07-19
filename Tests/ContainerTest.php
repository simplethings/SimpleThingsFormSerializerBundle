<?php
namespace SimpleThings\FormSerializerBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SimpleThings\FormSerializerBundle\DependencyInjection\SimpleThingsFormSerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\DependencyInjection\CompilerPass\EncoderPass;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $registry = new EncoderRegistry(array(new XmlEncoder, new JsonEncoder));
        $factory  = new FormFactory(new FormRegistry(array(
            new CoreExtension(),
            new SerializerExtension($registry)
        )));
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.bundles'     => array(),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__.'/../../../../' // src dir
        )));
        $loader = new SimpleThingsFormSerializerExtension();
        $container->registerExtension($loader);
        $container->set('form.factory', $factory);
        $loader->load(array(array()), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array(new ResolveDefinitionTemplatesPass(), new EncoderPass()));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        $this->assertInstanceOf('SimpleThings\FormSerializerBundle\Serializer\FormSerializer', $container->get('simple_things_form_serializer.form_serializer'));
        $this->assertInstanceOf('SimpleThings\FormSerializerBundle\Serializer\FormSerializer', $serializer = $container->get('form_serializer'));

        return $serializer;
    }

    /**
     * @depends testContainer
     */
    public function testSerializeFromContainer($serializer)
    {
        $comment = new Comment;
        $comment->message = "Test";

        $data = $serializer->serialize($comment, new CommentType(), "xml");

        $this->assertEquals("<?xml version=\"1.0\"?>\n<user><message>Test</message></user>\n", $data);
    }
}

class Comment
{
    public $message;
}

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => __NAMESPACE__ . '\\Comment',
            'serialize_xml_name'  => 'user',
        ));
    }

    public function getName()
    {
        return 'comment';
    }
}
