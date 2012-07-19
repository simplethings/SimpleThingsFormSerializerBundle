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

namespace SimpleThings\FormSerializerBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class EncoderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ( ! $container->hasDefinition('simple_things_form_serializer.serializer.encoder_registry')) {
            return;
        }

        $def  = $container->getDefinition('simple_things_form_serializer.serializer.encoder_registry');
        $args = $def->getArguments();

        foreach ($container->findTaggedServiceIds('simple_things_form_serializer.encoder') as $id => $attributes) {
            $args[0][] = new Reference($id);
        }

        $def->setArguments($args);
    }
}

