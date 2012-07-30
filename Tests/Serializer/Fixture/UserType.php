<?php
namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('email', 'email')
            ->add('birthday', 'date', array('widget' => 'single_text'))
            ->add('country', 'country')
            ->add('address', new AddressType())
            ->add('addresses', 'collection', array(
                'type'                 => new AddressType(),
                'allow_add'            => true,
                'serialize_xml_inline' => false,
                'serialize_xml_name'   => 'address'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'         => __NAMESPACE__ . '\\User',
            'serialize_xml_name' => 'user',
        ));
    }

    public function getName()
    {
        return 'user';
    }
}

