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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        return $builder->root('simple_things_form_serializer')
            ->children()
                ->booleanNode('include_root_in_json')->defaultFalse()->end()
                ->scalarNode('application_xml_root_name')->defaultNull()->end()
                ->scalarNode('naming_strategy')->defaultValue('camel_case')->end()
                ->arrayNode('encoders')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('xml')->defaultTrue()->end()
                        ->booleanNode('json')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}

