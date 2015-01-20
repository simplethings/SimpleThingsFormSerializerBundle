<?php
namespace SimpleThings\FormSerializerBundle\Tests;

use SimpleThings\FormSerializerBundle\DependencyInjection\CompilerPass\EncoderPass;
use SimpleThings\FormSerializerBundle\DependencyInjection\SimpleThingsFormSerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\FormSerializer;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContainerTest extends TestCase
{
    public function testContainer()
    {
        $factory = $this->createFormFactory();

        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug'       => false,
            'kernel.bundles'     => [],
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__ . '/../../../../' // src dir
        ]));
        $loader    = new SimpleThingsFormSerializerExtension();
        $container->registerExtension($loader);
        $container->set('form.factory', $factory);
        $loader->load([[]], $container);

        $container->getCompilerPassConfig()->setOptimizationPasses([
            new ResolveDefinitionTemplatesPass(),
            new EncoderPass()
        ]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        $serializer = $container->get('form_serializer');

        $this->assertInstanceOf(
            'SimpleThings\FormSerializerBundle\Serializer\FormSerializer',
            $container->get('simple_things_form_serializer.form_serializer')
        );
        $this->assertInstanceOf(
            'SimpleThings\FormSerializerBundle\Serializer\FormSerializer',
            $serializer
        );

        return $serializer;
    }

    /**
     * @depends testContainer
     */
    public function testSerializeFromContainer(FormSerializer $serializer)
    {
        $comment          = new Comment;
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
        $builder->add('message', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => __NAMESPACE__ . '\\Comment',
            'serialize_xml_name' => 'user',
        ]);
    }

    public function getName()
    {
        return 'comment';
    }
}
