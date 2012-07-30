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
}

