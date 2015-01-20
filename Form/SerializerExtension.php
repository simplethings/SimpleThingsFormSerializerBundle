<?php
/**
 * Beberlei Form Serializer
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace SimpleThings\FormSerializerBundle\Form;

use SimpleThings\FormSerializerBundle\Form\Extension\CollectionTypeExtension;
use SimpleThings\FormSerializerBundle\Form\Extension\SerializerTypeExtension;
use SimpleThings\FormSerializerBundle\Serializer\SerializerOptions;
use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class SerializerExtension extends AbstractExtension
{
    private $encoderRegistry;
    private $options;

    public function __construct(DecoderInterface $encoderRegistry, SerializerOptions $options = null)
    {
        $this->encoderRegistry = $encoderRegistry;
        $this->options         = $options ?: new SerializerOptions();
    }

    protected function loadTypeExtensions()
    {
        return [
            new SerializerTypeExtension($this->encoderRegistry, $this->options),
            new CollectionTypeExtension(),
        ];
    }
}

