<?php

namespace SimpleThingsFormSerializerBundle\Tests\Form\Extension;

use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class SerializerTypeExtensionTest extends \Symfony\Component\Form\Tests\FormIntegrationTestCase
{
    public function testSerializeOnlyIsRemovedFromView()
    {
        $formView = $this->factory->create(new ChildType())->createView();

        $this->assertFalse($formView->has('createdAt'));
    }

    public function getExtensions()
    {
        $registry = new EncoderRegistry(array(new JsonEncoder));

        $extensions = parent::getExtensions();
        $extensions[] = new SerializerExtension($registry);

        return $extensions;
    }
}


class ChildType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('createdAt', 'date', array(
                'serialize_only' => true,
            ))
        ;
    }

    public function getName()
    {
        return 'child_type';
    }
}
