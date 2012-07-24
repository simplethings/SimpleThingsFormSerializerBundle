<?php

namespace SimpleThingsFormSerializerBundle\Tests\Form\Extension;

use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use SimpleThings\FormSerializerBundle\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Extension\Core\CoreExtension;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class SerializerTypeExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->factory = new FormFactory($this->getExtensions());
        $this->form = $this->getBuilder()->getForm();
    }

    protected function getBuilder($name = 'name', EventDispatcherInterface $dispatcher = null)
    {
        return new FormBuilder($name, $this->factory, $dispatcher ?: $this->dispatcher);
    }

    public function testSerializeOnlyIsRemovedFromView()
    {
        $formView = $this->factory->create(new ChildType())->createView();

        $this->assertFalse($formView->has('createdAt'));
    }

    public function getExtensions()
    {
        $registry = new EncoderRegistry(array(new JsonEncoder));

        return array(
            new CoreExtension(),
            new SerializerExtension($registry),
        );
    }
}


class ChildType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
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
