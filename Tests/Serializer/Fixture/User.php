<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\SerializerBundle\Annotation as JMS;
use SimpleThings\FormSerializerBundle\Annotation\FormType;

/**
 * @JMS\ExclusionPolicy("none")
 * @FormType("SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\UserType")
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
