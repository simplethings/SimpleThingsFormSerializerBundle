<?php
namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class Address
{
    public $street;
    public $zipCode;
    public $city;
}
