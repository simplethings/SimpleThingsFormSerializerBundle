<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objects', 'collection', array('type' => new ObjectType(), 'serialize_xml_inline' => false, 'serialize_xml_name' => 'object'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Bar'
        ));
    }

    public function getName()
    {
        return 'bar';
    }
}
