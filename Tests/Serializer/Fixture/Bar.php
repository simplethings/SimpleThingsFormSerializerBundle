<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture;

use JMS\Serializer\Annotation as XML;

/**
 * @XML\XmlRoot("bar")
 */
class Bar
{
    /**
     * @XML\Type("array<SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Object>")
     * @XML\XmlList(entry="object", inline=false)
     */
    public $objects;
}