<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('object', new ObjectType())
            ->add('int', 'integer')
            ->add('date', 'datetime')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Foo'
        ));
    }

    public function getName()
    {
        return 'foo';
    }
}
