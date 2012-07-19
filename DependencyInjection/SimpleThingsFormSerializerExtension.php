<?php
/**
 * SimpleThings FormSerializerBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace SimpleThings\FormSerializerBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class SimpleThingsFormSerializerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setAlias('form_serializer', 'simple_things_form_serializer.form_serializer');
        $container->setAlias('simple_things_form_serializer.serializer.naming_strategy', 'simple_things_form_serializer.serializer.naming_strategy.' . $config['naming_strategy']);

        $container->setParameter('simple_things_form_serializer.options.include_root_in_json', $config['include_root_in_json']);
        $container->setParameter('simple_things_form_serializer.options.application_xml_root_name', $config['application_xml_root_name']);

        foreach ($config['encoders'] as $format => $enabled) {
            if ($enabled) {
                $def = $container->getDefinition('simple_things_form_serializer.serializer.encoder.' . $format);
                $def->addTag('simple_things_form_serializer.encoder');
            }
        }
    }
}

