<?php

namespace SimpleThings\FormSerializerBundle\Serializer\JMS;

use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Metadata\PropertyMetadata;
use JMS\SerializerBundle\Metadata\VirtualPropertyMetadata;

use SimpleThings\FormSerializerBundle\Annotation\FormType;

use Metadata\Driver\DriverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\Common\Annotations\Reader;

class FormMetadataDriver implements DriverInterface
{
    private $reader;
    private $formFactory;

    public function __construct(Reader $reader, FormFactoryInterface $formFactory)
    {
        $this->reader      = $reader;
        $this->formFactory = $formFactory;
    }

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($name = $class->getName());

        foreach ($this->reader->getClassAnnotations($class) as $annot) {
            if ($annot instanceof FormType) {
                $type  = $annot->getType();

                if (class_exists($type)) {
                    $type = new $type;
                }

                $form    = $this->formFactory->create($type, null, array());
                $options = $form->getConfig()->getOptions();

                $classMetadata->xmlRootName = $options['serialize_xml_name'];

                $propertiesMetadata = array();
                foreach ($form->getChildren() as $children) {
                    $childOptions = $children->getConfig()->getOptions();
                    $type = $children->getConfig()->getType();

                    $property         = $class->getProperty($children->getName());
                    $propertyMetadata = new PropertyMetadata($name, $property->getName());
                    #$propertyMetadata->setAccessor('public_method', null, null);

                    if ( !empty($childOptions['serialize_name'])) {
                        $propertyMetadata->serializedName = $childOptions['serialize_name'];
                    }

                    if ($type->getName() == "collection") {
                        $propertyMetadata->xmlCollection = true;
                        $propertyMetadata->xmlCollectionInline = $childOptions['serialize_xml_inline'];

                        if ( ! empty($childOptions['serialize_xml_name'])) {
                            $propertyMetadata->xmlEntryName = $childOptions['serialize_xml_name'];
                        }

                        $subForm = $this->formFactory->create($childOptions['type']);

                        $propertyMetadata->type = sprintf('array<%s>', $this->translateType($subForm));
                    } else if ($type->getName() == "choice") {
                        $propertyMetadata->type = $childOptions['multiple'] ? "array<string>" : "string";
                    } else if ($type->getName() == "entity") {
                        $propertyMetadata->type = $childOptions['multiple'] ? "array<string>" : "string";
                    } else {
                        $propertyMetadata->type = $this->translateType($children);
                    }

                    if ($childOptions['serialize_xml_attribute']) {
                        $propertyMetadata->xmlAttribute = true;
                    } else if ($childOptions['serialize_xml_value']) {
                        $propertyMetadata->xmlValue = true;
                    }

                    if ($childOptions['disabled']) {
                        $propertyMetadata->readOnly = true;
                    }

                    #var_dump($propertyMetadata);
                    $classMetadata->addPropertyMetadata($propertyMetadata);
                }
            }
        }

        return $classMetadata;
    }

    private function translateType($form)
    {
        $options = $form->getConfig()->getOptions();
        if ($options['data_class']) {
            return $options['data_class'];
        }

        switch ($form->getConfig()->getType()->getName()) {
            case 'date':
            case 'datetime':
            case 'time':
            case 'birthday';
                return 'DateTime';
            case 'number':
                return 'float';
            case 'checkbox':
                return 'boolean';
            default:
                return 'string';
        }
    }
}

