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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new BindRequestListener($this->encoderRegistry, $this->options));
    }

    public function finishView(FormViewInterface $view, FormInterface $form, array $options)
    {
        foreach ($form->getChildren() as $identifier => $child) {
            if (false == $child->getConfig()->getOption('serialize_only')) {
                continue;
            }

            $view->remove($identifier);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'serialize_name'          => false,
            'serialize_xml_name'      => 'entry',
            'serialize_xml_value'     => false,
            'serialize_xml_attribute' => false,
            'serialize_xml_inline'    => true,
            'serialize_only'          => false,
        ));
    }

    public function getExtendedType()
    {
        return 'form';
    }
}

