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

        $this->assertInstanceOf('JMS\SerializerBundle\Metadata\ClassMetadata', $metadata);
        $this->assertEquals(array('username', 'email', 'birthday', 'country', 'address', 'addresses'), array_keys($metadata->propertyMetadata));
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
        $user->addresses = array($address, $address);

        $xml = $serializer->serialize($user, 'xml');

        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<user>
  <username><![CDATA[beberlei]]></username>
  <email><![CDATA[kontakt@beberlei.de]]></email>
  <birthday>1984-03-18T00:00:00+0100</birthday>
  <country><![CDATA[DE]]></country>
  <address street="Somestreet 1" zip_code="12345" city="Bonn"/>
  <addresses>
    <address street="Somestreet 1" zip_code="12345" city="Bonn"/>
    <address street="Somestreet 1" zip_code="12345" city="Bonn"/>
  </addresses>
</user>

XML
            , $xml);

        $json = $serializer->serialize($user, 'json');

        $this->assertEquals(<<<JSON
{"username":"beberlei","email":"kontakt@beberlei.de","birthday":"1984-03-18T00:00:00+0100","country":"DE","address":{"street":"Somestreet 1","zip_code":12345,"city":"Bonn"},"addresses":[{"street":"Somestreet 1","zip_code":12345,"city":"Bonn"},{"street":"Somestreet 1","zip_code":12345,"city":"Bonn"}]}
JSON
            , $json);
    }
}

