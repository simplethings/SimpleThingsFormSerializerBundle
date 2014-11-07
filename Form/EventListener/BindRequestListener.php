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

namespace SimpleThings\FormSerializerBundle\Form\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

use SimpleThings\FormSerializerBundle\Serializer\EncoderRegistry;
use SimpleThings\FormSerializerBundle\Serializer\SerializerOptions;

class BindRequestListener implements EventSubscriberInterface
{
    private $decoder;
    private $options;

    public function __construct(DecoderInterface $decoder, SerializerOptions $options = null)
    {
        $this->decoder = $decoder;
        $this->options = $options ?: new SerializerOptions();
    }

    public static function getSubscribedEvents()
    {
        // High priority in order to supersede other listeners
        return array(FormEvents::PRE_BIND => array('preBind', 129));
    }

    public function preBind(FormEvent $event)
    {
        $form    = $event->getForm();
        $request = $event->getData();

        if ( ! $request instanceof Request) {
            return;
        }

        $format = $request->getContentType();

        if ( ! $this->decoder->supportsDecoding($format)) {
            return;
        }

        $content = $request->getContent();
        $options = $form->getConfig()->getOptions();
        $xmlName = !empty($options['serialize_xml_name']) ? $options['serialize_xml_name'] : 'entry';
        $data    = $this->decoder->decode($content, $format);

        if ( ($format === "json" && $this->options->getIncludeRootInJson()) ||
             ($format === "xml" && $this->options->getApplicationXmlRootName() && $this->options->getApplicationXmlRootName() !== $xmlName)) {
            $data = isset($data[$xmlName]) ? $data[$xmlName] : array();
        }

        $event->setData($this->unserializeForm($data, $form, $format == "xml", $request->getMethod() == "PATCH"));
    }

    private function unserializeForm($data, $form, $isXml, $isPatch)
    {
        if ($form->getConfig()->hasAttribute('serialize_collection_form')) {
            $form   = $form->getConfig()->getAttribute('serialize_collection_form');
            $result = array();

            if (!isset($data[0])) {
                $data = array($data); // XML special case
            }

            foreach ($data as $key => $child) {
                $result[$key] = $this->unserializeForm($child, $form, $isXml, $isPatch);
            }

            return $result;
        } else if ( ! $form->all()) {
            return $data;
        }

        $result = array();
        $namingStrategy = $this->options->getNamingStrategy();

        foreach ($form->all() as $child) {
            $options     = $child->getConfig()->getOptions();

            if (isset($options['disabled']) && $options['disabled']) {
                continue;
            }

            $name        = $options['serialize_name'] ?: $namingStrategy->translateName($child);
            $isAttribute = isset($options['serialize_xml_attribute']) && $options['serialize_xml_attribute'];

            if ($options['serialize_xml_value'] && isset($data['#'])) {
                $value = $data['#'];
            } else if (! $options['serialize_xml_inline'] && $isXml) {
                $value = isset($data[$name][$options['serialize_xml_name']])
                    ? $data[$name][$options['serialize_xml_name']]
                    : null;
            } else {
                $value = isset($data['@' . $name])
                    ? $data['@' . $name]
                    : (isset($data[$name]) ? $data[$name] : null);
            }

            // If we are PATCHing then don't fill in missing attributes with null
            $childValue = $this->unserializeForm($value, $child, $isXml, $isPatch);
            if (!($isPatch && is_null($childValue))) $result[$child->getName()] = $childValue;
        }

        return $result;
    }
}

