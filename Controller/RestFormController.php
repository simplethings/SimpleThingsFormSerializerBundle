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

namespace SimpleThings\FormSerializerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form Controller helps implementing Restful Controllers
 * using the Form component for serialization.
 *
 * @example
 *
 *   public function updateAction(User $user)
 *   {
 *       if ( ! $this->applyForm(new UserType, $user)) {
 *           return $this->renderForm($this->form);
 *       }
 *
 *       $userService = $this->get('user_service');
 *       $userService->update($user);
 *
 *       $this->flash('notice', 'User updated');
 *
 *       return $this->redirectRoute('user_edit', array('id' => $user->getId()));
 *   }
 */
abstract class RestFormController extends Controller
{
    /**
     * Currently worked on/with form instance. Is set when {@see applyForm()} is used.
     *
     * @var FormInterface
     */
    protected $form;

    /**
     * @return \SimpleThings\FormSerializerBundle\Serializer\FormSerializer
     */
    protected function getFormSerializer()
    {
        return $this->get('form_serializer');
    }

    /**
     * Apply a form type to a given object and check for validity of model.
     *
     * @param FormTypeInterface $type
     * @param object            $data
     * @param array             $options
     *
     * @return bool
     */
    protected function applyForm(FormTypeInterface $type, $data = null, array $options = array())
    {
        $this->form = $this->bindForm($type, $data, $options);

        return $this->form->isValid();
    }

    /**
     * Create and return a form (failure) response based on the HTTP Response format.
     *
     * @param FormInterface $data
     * @param array         $variables Additional data that is passed to an HTML view.
     *
     * @return Response
     */
    protected function renderForm(FormInterface $form = null, array $variables = array())
    {
        $form   = $form ?: $this->form;
        $format = $this->getRequest()->getRequestFormat();

        if ($format === "html") {
            $variables['form'] = $form->createView();
            $variables['data'] = $form->getData();

            return $variables;
        }

        $statusCode = 200;
        if ( ! $form->isValid()) {
            $statusCode = 412;
        }

        switch ($format) {
            case 'xml':
                $contentType = 'text/xml';
                break;
            case 'json':
                $contentType = 'application/json';
                break;
        }

        return new Response(
            $this->get('form_serializer')->serialize(null, $form, $format),
            $statusCode,
            array('Content-Type' => $contentType)
        );
    }

    /**
     * Simplifies the flash usage inside a controller.
     *
     * @return FlashBagInterface
     */
    protected function flash()
    {
        if ($this->getRequest()->getRequestFormat() !== "html") {
            return new FlashBag; // dummy flush-bag, to keep the fluent
        }

        $args = func_get_args();

        if (count($args) == 2) {
            $this->get('session')->getFlashBag()->add($args[0], $args[1]);
        }

        return $this->get('session')->getFlashBag();
    }

    /**
     * Shortcut for redirecting using route. Also if 201/204 status codes are
     * redirected, it will display an empty response with a location header
     * for non HTML format views, but also turn the response code into 301 if
     * it is an HTML view.
     *
     * @param string $routeName
     * @param mixed  $parameters
     * @param int    $statusCode
     * @param bool   $absolute
     *
     * @return RedirectResponse
     */
    protected function redirectRoute($routeName, $parameters = array(), $statusCode = 301, $absolute = false)
    {
        $link = $this->generateUrl($routeName, $parameters, $absolute);

        if ($statusCode === 201 || $statusCode === 204) {
            if ($this->getRequest()->getRequestFormat() !== "html") {
                return new Response("", $statusCode, array("Location" => $link));
            }
            $statusCode = 301;
        }

        return $this->redirect($link, $statusCode);
    }

    /**
     * Bind the current request against the given FormType and return the form.
     *
     * @param FormTypeInterface $type
     * @param mixed             $data
     * @param array             $options
     *
     * @return FormInterace
     */
    protected function bindForm(FormTypeInterface $type, $data = null, $options = array())
    {
        $form = $this->createForm($type, $data, $options);
        $form->bind($this->getRequest());

        return $form;
    }
}

