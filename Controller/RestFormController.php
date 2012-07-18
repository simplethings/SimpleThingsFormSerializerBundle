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

/**
 * Form Controller helps implementing Restful Controllers
 * using the Form component for serialization.
 *
 * @example
 *
 *   public function updateAction(User $user)
 *   {
 *       if ( ! $this->applyForm(new UserType, $user)) {
 *           $this->renderForm($this->form);
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
     * Apply a form type to a given object and check for validity of model.
     *
     * @param FormTypeInterface $type
     * @param object $data
     * @param array $options
     * @param string $dataName
     *
     * @return bool
     */
    protected function applyForm(FormTypeInterface $type, $data = null, array $options = array(), $dataName = null)
    {
        $this->form = $this->bindForm($type, $data, $options);

        return $this->form->isValid();
    }

    /**
     * Create and return a form (failure) response based on the HTTP Response format.
     *
     * @param FormInterface $data
     * @param array $variables Additional data that is passed to an HTML view.
     * @param string $dataName Alternative name for the form data variable in the view, if not the form name.
     *
     * @return Response
     */
    protected function renderForm(FormInterface $form = null, array $variables = array(), $dataName = null)
    {
        $form                 = $form ?: $this->form;
        $dataName             = $dataName ?: $form->getName();
        $variables['form']    = $form->createView();
        $variables[$dataName] = $form->getData();

        return $variables;
    }

    /**
     * Simplifies the flash usage inside a controller.
     *
     * @param string $type
     * @param string $message
     * @return FlashBagInterface
     */
    protected function flash()
    {
        $args = func_get_args();
        if (count($args) == 2) {
            $this->get('session')->getFlashBag()->add($args[0], $args[1]);
        }
        return $this->get('session')->getFlashBag();
    }

    /**
     * Shortcut for redirecting using route.
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
        return $this->redirect($this->generateUrl($routeName, $parameters, $absolute), $statusCode);
    }

    /**
     * Bind the current request against the given FormType and return the form.
     *
     * @param FormTypeInterface $type
     * @param mixed $data
     * @param array $options
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

