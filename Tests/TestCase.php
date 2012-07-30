<?php

namespace SimpleThings\FormSerializerBundle\Tests;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\ResolvedFormTypeFactory;

use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\FormSerializer;
use SimpleThings\FormSerializerBundle\Serializer\SerializerOptions;

use JMS\SerializerBundle\Serializer\Handler\DeserializationHandlerInterface;
use JMS\SerializerBundle\Serializer\Handler\SerializationHandlerInterface;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Serializer\XmlDeserializationVisitor;
use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Serializer\JsonDeserializationVisitor;
use JMS\SerializerBundle\Serializer\Handler\ObjectBasedCustomHandler;
use JMS\SerializerBundle\Serializer\Handler\DateTimeHandler;
use JMS\SerializerBundle\Serializer\Handler\ArrayCollectionHandler;
use JMS\SerializerBundle\Serializer\Handler\DoctrineProxyHandler;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Metadata\Driver\AnnotationDriver;
use JMS\SerializerBundle\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\SerializerBundle\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\SerializerBundle\Serializer\JsonSerializationVisitor;
use JMS\SerializerBundle\Serializer\Serializer as JMSSerializer;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Metadata\MetadataFactory;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function createFormFactory(SerializerOptions $options = null)
    {
        $registry = new EncoderRegistry(array(new XmlEncoder, new JsonEncoder));
        $factory = new FormFactory(new FormRegistry(array(
                        new CoreExtension(),
                        new SerializerExtension($registry)
                        ), new ResolvedFormTypeFactory), new ResolvedFormTypeFactory);
        return $factory;
    }

    public function createFormSerializer(SerializerOptions $options = null)
    {
        $registry = new EncoderRegistry(array(new XmlEncoder, new JsonEncoder), $options);
        $factory = new FormFactory(new FormRegistry(array(
                        new CoreExtension(),
                        new SerializerExtension($registry)
                        ), new ResolvedFormTypeFactory), new ResolvedFormTypeFactory);
        $formSerializer = new FormSerializer($factory, $registry, $options);
        return $formSerializer;
    }

    public function createJmsSerializer()
    {
        $namingStrategy    = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $objectConstructor = new UnserializeObjectConstructor();

        $customSerializationHandlers = array(
            new DateTimeHandler(),
            new DoctrineProxyHandler(),
        );

        $customDeserializationHandlers = array(
            new DateTimeHandler(),
            new ArrayCollectionHandler(),
        );

        $serializationVisitors = array(
            'json' => new JsonSerializationVisitor($namingStrategy, $customSerializationHandlers),
            'xml'  => new XmlSerializationVisitor($namingStrategy, $customSerializationHandlers),
        );
        $deserializationVisitors = array(
            'json' => new JsonDeserializationVisitor($namingStrategy, $customDeserializationHandlers, $objectConstructor),
            'xml'  => new XmlDeserializationVisitor($namingStrategy, $customDeserializationHandlers, $objectConstructor),
        );

        $factory = $this->createJmsMetadataFactory();
        return new JMSSerializer($factory, $serializationVisitors, $deserializationVisitors);
    }

    public function createJmsMetadataFactory()
    {
        $fileLocator = new \Metadata\Driver\FileLocator(array());
        $driver      = new \Metadata\Driver\DriverChain(array(
            new \JMS\SerializerBundle\Metadata\Driver\YamlDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\XmlDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\PhpDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\AnnotationDriver(new \Doctrine\Common\Annotations\AnnotationReader())
        ));
        return new MetadataFactory($driver);
    }

    /**
     * Pretty print JSON
     *
     * @link http://www.php.net/manual/en/function.json-encode.php#80339
     */
    protected function formatJson($json)
    {
        $tab = "  ";
        $new_json = "";
        $indent_level = 0;
        $in_string = false;

        $json_obj = json_decode($json);

        if($json_obj === false)
            return false;

        $json = json_encode($json_obj);
        $len = strlen($json);

        for($c = 0; $c < $len; $c++)
        {
            $char = $json[$c];
            switch($char)
            {
                case '{':
                case '[':
                    if(!$in_string)
                    {
                        $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                        $indent_level++;
                    }
                    else
                    {
                        $new_json .= $char;
                    }
                    break;
                case '}':
                case ']':
                    if(!$in_string)
                    {
                        $indent_level--;
                        $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                    }
                    else
                    {
                        $new_json .= $char;
                    }
                    break;
                case ',':
                    if(!$in_string)
                    {
                        $new_json .= ",\n" . str_repeat($tab, $indent_level);
                    }
                    else
                    {
                        $new_json .= $char;
                    }
                    break;
                case ':':
                    if(!$in_string)
                    {
                        $new_json .= ": ";
                    }
                    else
                    {
                        $new_json .= $char;
                    }
                    break;
                case '"':
                    if($c > 0 && $json[$c-1] != '\\')
                    {
                        $in_string = !$in_string;
                    }
                default:
                    $new_json .= $char;
                    break;
            }
        }

        return $new_json;
    }

    protected function formatXml($xml)
    {
        $dom = new \DOMDocument;
        $dom->loadXml($xml);
        $dom->formatOutput = true;

        return $dom->saveXml();
    }
}

