<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use SimpleThings\FormSerializerBundle\Tests\TestCase;
use SimpleThings\FormSerializerBundle\Serializer\JMS\FormMetadataDriver;

class JmsFormMetadataDriverTest extends TestCase
{
    public function testLoadMetadata()
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $driver = new FormMetadataDriver($reader, $this->createFormFactory());

        $reflClass = new \ReflectionClass('SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\User');
        $metadata  = $driver->loadMetadataForClass($reflClass);

        var_dump($metadata);
    }
}

