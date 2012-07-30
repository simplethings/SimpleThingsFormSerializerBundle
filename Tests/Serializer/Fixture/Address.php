<?php
namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\SerializerBundle\Annotation as JMS;
use SimpleThings\FormSerializerBundle\Annotation\FormType;

/**
 * @JMS\ExclusionPolicy("none")
 * @FormType("SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\AddressType")
 */
class Address
{
    public $street;
    public $zipCode;
    public $city;
}
