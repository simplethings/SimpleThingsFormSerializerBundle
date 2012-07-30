<?php
/**
 * SimpleThings FormSerializerBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace SimpleThings\FormSerializerBundle\Tests\Serializer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use SimpleThings\FormSerializerBundle\Tests\TestCase;

class FormSerializerTest extends TestCase
{
    public function testFunctional()
    {
        $factory = $this->createFormFactory();

        $address          = new Address();
        $address->street  = "Somestreet 1";
        $address->zipCode = 12345;
        $address->city    = "Bonn";

        $user            = new User();
        $user->username  = "beberlei";
        $user->email     = "kontakt@beberlei.de";
        $user->birthday  = new \DateTime("1984-03-18");
        $user->gender    = 'male';
        $user->interests = array('sport', 'reading');
        $user->country   = "DE";
        $user->address   = $address;

        $builder = $factory->createBuilder('form', null, array('data_class' => __NAMESPACE__ . '\\User', 'serialize_xml_name' => 'user'));
        $builder
            ->add('username', 'text')
            ->add('email', 'email')
            ->add('birthday', 'date', array('widget' => 'single_text'))
            ->add('gender', 'choice', array('choices' => array('male' => 'Male', 'female' => 'Female')))
            ->add('interests', 'choice', array('choices' => array('sport' => 'Sports', 'reading' => 'Reading'), 'multiple' => true, 'serialize_xml_inline' => false, 'serialize_xml_name' => 'interest'))
            ->add('country', 'country', array('serialize_only' => true))
            ->add('address', null, array('compound' => true, 'data_class' => __NAMESPACE__ . '\\Address'))
            ;

        $addressBuilder = $builder->get('address');
        $addressBuilder
            ->add('street', 'text', array('serialize_xml_value' => true))
            ->add('zipCode', 'text', array('serialize_xml_attribute' => true))
            ->add('city', 'text', array('serialize_xml_attribute' => true))
            ;

        $formSerializer = $this->createFormSerializer();
        $xml           = $formSerializer->serialize($user, $builder, 'xml');

        $dom = new \DOMDocument;
        $dom->loadXml($xml);
        $dom->formatOutput = true;
        $xml = $dom->saveXml();

        $this->assertEquals(<<<XML
<?xml version="1.0"?>
<user>
  <username>beberlei</username>
  <email>kontakt@beberlei.de</email>
  <birthday>1984-03-18</birthday>
  <gender>male</gender>
  <interests>
    <interest>sport</interest>
    <interest>reading</interest>
  </interests>
  <country>DE</country>
  <address zip_code="12345" city="Bonn">Somestreet 1</address>
</user>

XML
            , $xml);

        $json = $formSerializer->serialize($user, $builder, 'json');
        /*
           {
           "username":"beberlei",
           "email":"kontakt@beberlei.de",
           "birthday":"1984-03-18",
           "country":"DE",
           "address":{"street":"Somestreet 1","zip_code":"12345","city":"Bonn"}
           }*/

        $user2 = new User;
        $form = $builder->getForm();
        $form->setData($user2);

        $request = new Request(array(), array(),array(),array(),array(),array(
                    'CONTENT_TYPE' => 'text/xml',
                    ), $xml);

        $form->bind($request);

        $user3 = new User;
        $form = $builder->getForm();
        $form->setData($user3);

        $request = new Request(array(), array(),array(),array(),array(),array(
                    'CONTENT_TYPE' => 'application/json',
                    ), $json);
        $form->bind($request);

        $this->assertEquals($user2, $user);
        $this->assertEquals($user3, $user);
    }

    public function testSerializeCollection()
    {
        $factory = $this->createFormFactory();

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

        $formSerializer = $this->createFormSerializer();
        $xml           = $formSerializer->serialize($user, $type = new UserType(), 'xml');

        $dom = new \DOMDocument;
        $dom->loadXml($xml);
        $dom->formatOutput = true;
        $xml = $dom->saveXml();

        $this->assertEquals(<<<XML
<?xml version="1.0"?>
<user>
  <username>beberlei</username>
  <email>kontakt@beberlei.de</email>
  <birthday>1984-03-18</birthday>
  <country>DE</country>
  <address street="Somestreet 1" zip_code="12345" city="Bonn"/>
  <addresses>
    <address street="Somestreet 1" zip_code="12345" city="Bonn"/>
    <address street="Somestreet 1" zip_code="12345" city="Bonn"/>
  </addresses>
</user>

XML
        , $xml);

        $request = new Request(array(), array(),array(),array(),array(),array(
                    'CONTENT_TYPE' => 'text/xml',
                    ), $xml);

        $user2 = new User();
        $form = $factory->create($type, $user2);
        $form->bind($request);

        $this->assertEquals(2, count($user2->addresses));
    }

    public function testSerializeErrors()
    {
        $factory = $this->createFormFactory();

        $user2 = new User();
        $form = $factory->create(new UserType(), $user2);
        $xml = <<<XML
<?xml version="1.0"?>
<user>
  <username>beberlei</username>
  <email>kontakt@beberlei.de</email>
  <birthday>1984-03-18</birthday>
  <gender>male</gender>
  <interests>
    <interest>sport</interest>
    <interest>reading</interest>
  </interests>
  <country>DE</country>
  <address zip_code="12345" city="Bonn">Somestreet 1</address>
</user>

XML;

        $request = new Request(array(), array(),array(),array(),array(),array(
                    'CONTENT_TYPE' => 'text/xml',
                    ), $xml);
        $form->bind($request);

        $form->addError(new FormError("foo"));
        $form->addError(new FormError("bar"));
        $form->get('username')->addError(new FormError("bar"));
        $form->get('email')->addError(new FormError("bar"));

        $formSerializer = $this->createFormSerializer();
        $xml = $formSerializer->serialize(null, $form, 'xml');

        $this->assertEquals("<?xml version=\"1.0\"?>\n<form><error>foo</error><error>bar</error><children><username><error>bar</error></username><email><error>bar</error></email></children></form>\n", $xml);
    }
}

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

class Address
{
    public $street;
    public $zipCode;
    public $city;
}

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text')
            ->add('email', 'email')
            ->add('birthday', 'date', array('widget' => 'single_text'))
            ->add('country', 'country')
            ->add('address', new AddressType())
            ->add('addresses', 'collection', array(
                'type'               => new AddressType(),
                'allow_add'          => true,
                'serialize_xml_inline'   => false,
                'serialize_xml_name' => 'address'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => __NAMESPACE__ . '\\User',
            'serialize_xml_name'  => 'user',
        ));
    }

    public function getName()
    {
        return 'user';
    }
}

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', 'text', array('serialize_xml_attribute' => true))
            ->add('zipCode', 'text', array('serialize_xml_attribute' => true))
            ->add('city', 'text', array('serialize_xml_attribute' => true))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => __NAMESPACE__ . '\\Address',
        ));
    }

    public function getName()
    {
        return 'address';
    }
}
