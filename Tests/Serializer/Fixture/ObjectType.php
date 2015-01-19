<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ObjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('foo', 'text')
            ->add('bar', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Object'
        ));
    }

    public function getName()
    {
        return 'object';
    }
}
