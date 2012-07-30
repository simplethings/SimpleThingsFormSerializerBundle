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
                $group = $annot->getGroup();

                if (class_exists($type)) {
                    $type = new $type;
                }

                $form    = $this->formFactory->create($type, null, array());
                $options = $form->getConfig()->getOptions();

                $classMetadata->xmlRootName = $options['serialize_xml_name'];

                $propertiesMetadata = array();
                foreach ($form->getChildren() as $children) {
                    $childOptions = $children->getConfig()->getOptions();

                    $property         = $class->getProperty($children->getName());
                    $propertyMetadata = new PropertyMetadata($name, $property->getName());

                    var_dump($propertyMetadata);
                }
            }
        }

        return $classMetadata;
    }
}

