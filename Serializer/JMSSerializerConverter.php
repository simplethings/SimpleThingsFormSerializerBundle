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

namespace SimpleThings\FormSerializerBundle\Serializer;

use Metadata\MetadataFactoryInterface;

/**
 * Converts JMSSerializer Metadata for classes into PHP code for forms.
 */
class JMSSerializerConverter
{
    private $metadataFactory;
    private $typeMap = array(
        'string'   => 'text',
        'boolean'  => 'checkbox',
        'integer'  => 'integer',
        'double'   => 'number',
        'DateTime' => 'datetime',
    );

    static private $template = <<<'PHP'
<?php

namespace {{namespace}};

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class {{class}} extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            {{build}}
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $options->setDefaults(array(
            {{options}
        ));
    }

    public function getName()
    {
        return '{{name}}';
    }
}

PHP;

    public function __construct(MetadataFactoryInterface $factory)
    {
        $this->metadataFactory = $factory;
    }

    private function getType($type, $recursive = false)
    {
        if (isset(self::$typeMap[$type])) {
            return self::$typeMap[$type];
        } else if (strpos($type, "array<") === 0) {
            if ( ! $recursive) {
                return 'collection';
            }

            if (false === $pos = strpos($type, ',', 6)) {
                $listType = substr($type, 6, -1);
            } else {
                $keyType = trim(substr($type, 6, $pos - 6));
                $listType = trim(substr($type, $pos+1, -1));
            }

            return $this->getType($listType);
        } else if (class_exists($type)) {

            $parts = explode("\\", $type);
            return "new " . end($parts) . "Type()";
        }
    }

    public function generateFormPhpCode($className)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        $lines = array();

        $defaults = array(
            "'data_class' => '" . $metadata->type . "'";
        );
        if ($metadata->xmlRootName) {
            $efaults[] = "'serialize_xml_name' => '" . $metadata->xmlRootName . "'";
        }

        $builder = array();
        foreach ($metadata->propertyMetadata as $property) {
            $options = array();

            if ($property->xmlCollection) {
                $options[] = "'type' => " . $this->getType($property->type, true);
                if ( ! $property->xmlCollectionInline) {
                    $options[] = "'serialize_xml_inline' => false,";
                }

                if ($property->xmlEntryName) {
                    $options[] = "'serialize_xml_name' => '" . $property->xmlEntryName . "',";
                }
            }

            if ($property->xmlAttribute) {
                $options[] = "'serialize_xml_attribute' => true";
            }

            if ($property->xmlValue) {
                $options[] = "'serialize_xml_value' => true";
            }

            if ($property->readOnly) {
                $options[] = "'read_only' => true";
            }

            $options = $options ? ", array(" . implode(", ", $options) . ")" : "";

            $type = $this->getType($property->type);
            $builder[] = "->add('" . $property->name . "', '" . $type . "'" . $options . ")";
        }

        // TODO: Replace
        return "";
    }
}

