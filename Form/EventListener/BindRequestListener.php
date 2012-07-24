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
use Symfony\Component\Form\Event\FilterDataEvent;
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
        return array(FormEvents::BIND_CLIENT_DATA => array('bindClientData', 129));
    }

    public function bindClientData(FilterDataEvent $event)
    {
        $form    = $event->getForm();
        $request = $event->getData();

        if ( ! $request instanceof Request) {
            return;
        }

        $format = $request->getFormat($request->server->get('CONTENT_TYPE'));

        if ( ! $this->decoder->supportsDecoding($format)) {
            return;
        }

        $content = $request->getContent();
        $options = array(
            'serialize_name' => false,
            'serialize_xml_name' => 'entry',
            'serialize_xml_value' => false,
            'serialize_xml_attribute' => false,
            'serialize_xml_inline' => true,
            'serialize_only' => false,
        );
        $xmlName = !empty($options['serialize_xml_name']) ? $options['serialize_xml_name'] : 'entry';
        $data    = $this->decoder->decode($content, $format);

        if ( ($format === "json" && $this->options->getIncludeRootInJson()) ||
             ($format === "xml" && $this->options->getApplicationXmlRootName() && $this->options->getApplicationXmlRootName() !== $xmlName)) {
            $data = isset($data[$xmlName]) ? $data[$xmlName] : array();
        }

        $event->setData($this->unserializeForm($data, $form));
    }

    private function unserializeForm($data, $form)
    {
        if ($form->hasAttribute('serialize_collection_form')) {
            $form   = $form->getAttribute('serialize_collection_form');
            $result = array();

            if (!isset($data[0])) {
                $data = array($data); // XML special case
            }

            foreach ($data as $key => $child) {
                $result[$key] = $this->unserializeForm($child, $form);
            }

            return $result;
        } else if ( ! $form->hasChildren()) {
            return $data;
        }

        $result = array();
        $namingStrategy = $this->options->getNamingStrategy();

        foreach ($form->getChildren() as $child) {
            $options = array(
                'serialize_name' => $child->getAttribute('serialize_name'),
                'serialize_xml_name' => $child->getAttribute('serialize_xml_name'),
                'serialize_xml_value' => $child->getAttribute('serialize_xml_value'),
                'serialize_xml_attribute' => $child->getAttribute('serialize_xml_attribute'),
                'serialize_xml_inline' => $child->getAttribute('serialize_xml_inline'),
                'disabled' => $child->isReadOnly(),
            );

            if (isset($options['disabled']) && $options['disabled']) {
                continue;
            }

            $name        = $options['serialize_name'] ?: $namingStrategy->translateName($child);
            $isAttribute = isset($options['serialize_xml_attribute']) && $options['serialize_xml_attribute'];

            if ($options['serialize_xml_value'] && isset($data['#'])) {
                $value = $data['#'];
            } else if (! $options['serialize_xml_inline']) {
                $value = isset($data[$name][$options['serialize_xml_name']])
                    ? $data[$name][$options['serialize_xml_name']]
                    : null;
            } else {
                $value = isset($data['@' . $name])
                    ? $data['@' . $name]
                    : (isset($data[$name]) ? $data[$name] : null);
            }

            $result[$child->getName()] = $this->unserializeForm($value, $child);
        }

        return $result;
    }
}

