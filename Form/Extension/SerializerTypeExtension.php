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

namespace SimpleThings\FormSerializerBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

use SimpleThings\FormSerializerBundle\Form\EventListener\BindRequestListener;
use SimpleThings\FormSerializerBundle\Serializer\SerializerOptions;

class SerializerTypeExtension extends AbstractTypeExtension
{
    private $encoderRegistry;
    private $options;

    public function __construct(DecoderInterface $encoderRegistry, SerializerOptions $options = null)
    {
        $this->encoderRegistry = $encoderRegistry;
        $this->options         = $options ?: new SerializerOptions();
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->addEventSubscriber(new BindRequestListener($this->encoderRegistry, $this->options));

        // Add the options as attributes so we have access to them later on.
        $builder->setAttribute('serialize_name', $options['serialize_name']);
        $builder->setAttribute('serialize_xml_name', $options['serialize_xml_name']);
        $builder->setAttribute('serialize_xml_value', $options['serialize_xml_value']);
        $builder->setAttribute('serialize_xml_attribute', $options['serialize_xml_attribute']);
        $builder->setAttribute('serialize_xml_inline', $options['serialize_xml_inline']);
        $builder->setAttribute('serialize_only', $options['serialize_only']);
    }

    public function buildViewButtomUp(FormView $view, FormInterface $form, array $options)
    {
        foreach ($form->getChildren() as $identifier => $child) {
            if (false == $child->getConfig()->getOption('serialize_only')) {
                continue;
            }

            $view->remove($identifier);
        }
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'serialize_name'          => false,
            'serialize_xml_name'      => 'entry',
            'serialize_xml_value'     => false,
            'serialize_xml_attribute' => false,
            'serialize_xml_inline'    => true,
            'serialize_only'          => false,
        );
    }

    public function getExtendedType()
    {
        return 'field';
    }
}

