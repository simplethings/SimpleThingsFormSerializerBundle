<?php

namespace SimpleThings\FormSerializerBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as BaseJsonEncoder;
use SimpleThings\FormSerializerBundle\Serializer\SupportsInterface;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
class JsonEncoder extends BaseJsonEncoder implements SupportsInterface
{
    public function supportsEncoding($format)
    {
        return 'json' === $format;
    }

    public function supportsDecoding($format)
    {
        return 'json' === $format;
    }
}
