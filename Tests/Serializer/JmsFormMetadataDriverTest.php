<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use SimpleThings\FormSerializerBundle\Tests\TestCase;
use SimpleThings\FormSerializerBundle\Serializer\JMS\FormMetadataDriver;

use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\User;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Address;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\UserType;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\AddressType;

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

    public function testSerialize()
    {
        $serializer = $this->createJmsSerializer(true);

        $address          = new Address();
        $address->street  = "Somestreet 1";
        $address->zipCode = 12345;
        $address->city    = "Bonn";

        $user            = new User();
        $user->username  = "beberlei";
        $user->email     = "kontakt@beberlei.de";
        $user->birthday  = new \DateTime("1984-03-18");
        $user->gender    = 'male';
        $user->interests = array('sport', 'reading');
        $user->country   = "DE";
        $user->address   = $address;

        $xml = $serializer->serialize($user, 'xml');

        var_dump($xml);
    }
}

