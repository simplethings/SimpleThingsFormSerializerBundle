<?php

namespace SimpleThings\FormSerializerBundle\Tests\Form\Extension;

use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class SerializerTypeExtensionTest extends FormIntegrationTestCase
{
    public function testSerializeOnlyIsRemovedFromView()
    {
        $formView = $this->factory->create(new ChildType())->createView();

        $this->assertFalse(array_key_exists('createdAt', $formView->children));
    }

    public function getExtensions()
    {
        $registry = new EncoderRegistry([new JsonEncoder()]);

        $extensions   = parent::getExtensions();
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
            ->add('createdAt', 'date', [
                'serialize_only' => true,
            ]);
    }

    public function getName()
    {
        return 'child_type';
    }
}
