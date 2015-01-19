<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\Serializer\Annotation as XML;

/**
 * @XML\XmlRoot("foo")
 */
class Foo
{
    /**
     * @XML\Type("SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Object")
     */
    public $object;

    /**
     * @XML\Type("integer")
     */
    public $int;

    /**
     * @XML\Type("DateTime")
     */
    public $date;
}