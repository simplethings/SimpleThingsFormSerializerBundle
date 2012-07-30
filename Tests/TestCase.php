<?php

namespace SimpleThings\FormSerializerBundle\Tests;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Form\Extension\Core\CoreExtension;

use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\FormSerializer;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function createFormFactory()
    {
        $registry = new EncoderRegistry(array(new XmlEncoder, new JsonEncoder));
        $factory = new FormFactory(new FormRegistry(array(
                        new CoreExtension(),
                        new SerializerExtension($registry)
                        )));
        return $factory;
    }

    public function createFormSerializer()
    {
        $registry = new EncoderRegistry(array(new XmlEncoder, new JsonEncoder));
        $factory = new FormFactory(new FormRegistry(array(
                        new CoreExtension(),
                        new SerializerExtension($registry)
                        )));
        $formSerializer = new FormSerializer($factory, $registry);
        return $formSerializer;
    }
}

