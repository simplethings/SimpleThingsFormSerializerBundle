<?php

namespace SimpleThings\FormSerializerBundle\Serializer;

/**
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 */
interface SupportsInterface
{
    public function supportsDecoding($format);

    public function supportsEncoding($format);
}
