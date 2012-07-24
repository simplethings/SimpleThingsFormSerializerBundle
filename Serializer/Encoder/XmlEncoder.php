<?php

namespace SimpleThings\FormSerializerBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder as BaseXmlEncoder;
use SimpleThings\FormSerializerBundle\Serializer\SupportsInterface;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class XmlEncoder extends BaseXmlEncoder implements SupportsInterface
{
    public function supportsEncoding($format)
    {
        return 'xml' === $format;
    }

    public function supportsDecoding($format)
    {
        return 'xml' === $format;
    }
}
