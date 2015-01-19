<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\Serializer\Annotation as XML;

/**
 * @XML\XmlRoot("test")
 */
class Object
{
    public $foo;
    public $bar;
}