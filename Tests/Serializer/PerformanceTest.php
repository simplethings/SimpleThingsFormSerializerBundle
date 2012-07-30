<?php

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use SimpleThings\FormSerializerBundle\Tests\TestCase;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\User;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\Address;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\UserType;
use SimpleThings\FormSerializerBundle\Tests\Serializer\Fixture\AddressType;

/**
 * @group performance
 */
class PerformanceTest extends TestCase
{
    public function testSerializeList20Elements()
    {
        $address          = new Address();
        $address->street  = "Somestreet 1";
        $address->zipCode = 12345;
        $address->city    = "Bonn";

        $user           = new User();
        $user->username = "beberlei";
        $user->email    = "kontakt@beberlei.de";
        $user->birthday = new \DateTime("1984-03-18");
        $user->country  = "DE";
        $user->address  = $address;
        $user->addresses = array($address, $address);

        $list = array();
        for ($i = 0; $i < 20; $i++) {
            $list[] = $user;
        }

        $formSerializer = $this->createFormSerializer();

        $start = microtime(true);
        $xml = $formSerializer->serializeList($list, new UserType(), 'xml', 'users');
        echo number_format(microtime(true) - $start, 4) . "\n";

        #echo $this->formatXml($xml);

        $jmsSerializer = $this->createJmsSerializer();
        $start = microtime(true);
        $xml = $jmsSerializer->serialize($list, 'xml');
        echo number_format(microtime(true) - $start, 4) . "\n";

        #echo $this->formatXml($xml);
    }
}

