<?php
namespace SimpleThings\FormSerializerBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;

use SimpleThings\FormSerializerBundle\DependencyInjection\SimpleThingsFormSerializerExtension;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $factory = new FormFactory(new FormRegistry(array()));
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

        $container->getCompilerPassConfig()->setOptimizationPasses(array(new ResolveDefinitionTemplatesPass()));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        $this->assertInstanceOf('SimpleThings\FormSerializerBundle\Serializer\FormSerializer', $container->get('simple_things_form_serializer.form_serializer'));
        $this->assertInstanceOf('SimpleThings\FormSerializerBundle\Serializer\FormSerializer', $container->get('form_serializer'));
    }
}

