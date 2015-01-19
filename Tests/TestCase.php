<?php

namespace SimpleThings\FormSerializerBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\SerializerBuilder;
use SimpleThings\FormSerializerBundle\Form\SerializerExtension;
use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use SimpleThings\FormSerializerBundle\Serializer\FormSerializer;
use SimpleThings\FormSerializerBundle\Serializer\SerializerOptions;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function createFormFactory(SerializerOptions $options = null)
    {
        $registry = $this->createEncoderRegistry();
        $factory  = new FormFactory(
            new FormRegistry(
                [new CoreExtension(), new SerializerExtension($registry)], new ResolvedFormTypeFactory()
            ),
            new ResolvedFormTypeFactory()
        );

        return $factory;
    }

    public function createFormSerializer(SerializerOptions $options = null)
    {
        $registry = $this->createEncoderRegistry();
        $factory  = $this->createFormFactory();

        return new FormSerializer($factory, $registry, $options);
    }

    public function createJmsSerializer()
    {
        return SerializerBuilder::create()
            ->addDefaultDeserializationVisitors()
            ->addDefaultHandlers()
            ->addDefaultListeners()
            ->addDefaultSerializationVisitors()
            ->build();
    }

    /**
     * Pretty print JSON
     *
     * @link http://www.php.net/manual/en/function.json-encode.php#80339
     */
    protected function formatJson($json)
    {
        $tab          = "  ";
        $new_json     = "";
        $indent_level = 0;
        $in_string    = false;

        $json_obj = json_decode($json);

        if ($json_obj === false) {
            return false;
        }

        $json = json_encode($json_obj);
        $len  = strlen($json);

        for ($c = 0; $c < $len; $c++) {
            $char = $json[$c];
            switch ($char) {
                case '{':
                case '[':
                    if (! $in_string) {
                        $new_json .= $char . "\n" . str_repeat($tab, $indent_level + 1);
                        $indent_level++;
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case '}':
                case ']':
                    if (! $in_string) {
                        $indent_level--;
                        $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case ',':
                    if (! $in_string) {
                        $new_json .= ",\n" . str_repeat($tab, $indent_level);
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case ':':
                    if (! $in_string) {
                        $new_json .= ": ";
                    } else {
                        $new_json .= $char;
                    }
                    break;
                case '"':
                    if ($c > 0 && $json[$c - 1] != '\\') {
                        $in_string = ! $in_string;
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

    /**
     * @return EncoderRegistry
     */
    protected function createEncoderRegistry()
    {
        return new EncoderRegistry([new XmlEncoder(), new JsonEncoder()]);
    }
}

