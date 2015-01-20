<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class User
{
    public $username;
    public $email;
    public $country;
    public $gender;
    public $interests;
    public $birthday;
    public $addresses;
    public $created;
    public $address;
}
