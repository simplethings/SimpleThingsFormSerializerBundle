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

namespace SimpleThings\FormSerializerBundle\Annotation;

/**
 * Register form type that is responsible for serializing the annotated entity.
 *
 * @Annotation
 * @Target("CLASS")
 */
class FormType
{
    /**
     * Class or form-type name that is registered in Form Factory.
     *
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $group = 'default';

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->type = $values['value'];
        }
        if (isset($values['group'])) {
            $this->group = $values['group'];
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function getGroup()
    {
        return $this->group;
    }
}

