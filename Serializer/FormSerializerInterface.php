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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Serializes object graphs based on form types.
 */
interface FormSerializerInterface
{
    /**
     * Serialize a list of objects, where each element is serialized based on a
     * form type.
     *
     * @param array|\Traversable $list
     * @param FormTypeInterface  $type
     * @param string             $format
     * @param string             $xmlRootName
     *
     * @return string
     */
    public function serializeList($list, $type, $format, $xmlRootName = 'entries');

    /**
     * Serialize an object based on a form type, form builder or form instance.
     *
     * @param mixed                                                $object
     * @param FormTypeInterface|FormBuilderInterface|FormInterface $typeBuilder
     * @param string                                               $format
     *
     * @return string
     */
    public function serialize($object, $typeBuilder, $format);
}

