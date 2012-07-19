# FormSerializerBundle

Bundle that helps solving the Serializer/Form component API missmatch. This missmatch leads to non-reusable
code in controllers, bloating every application by reimplementing everything over
and over again. Currently its nearly impossible to re-use REST-API calls and HTML/Form-based submits
in the same controller action. Additionally all the current serializer components share a
common flaw: They cannot deserialize (update) into existing object graphs. Updating 
object graphs is a problem the Form component already solves (perfectly!).

This bundle solves these issues by hooking into the Form Framework. It allows to implement serializers for objects, using form types. For deserializing
it uses the exact same API as "usual" form requests.

The Form component is a very good serialization library, but up to now it only
had one implementation: HTML Forms. This bundle adds support for hooking
the Serializer Encoders into this process, XML and JSON supported by default.

## New Form Options

Using this bundle you gain new form type options. The "form" field is overwritten to have the following additional configuration keys:

- `serialize_xml_name` - Specifies the root xml name or the list entry xml name of elements, depending on its definition on a parent or child element. (Default: entry)
- `serialize_xml_value` - If true, this field will be the xml value of the parent field. Useful if you have small embedded types that have some attributes and one value. (Default: false)
- `serialize_xml_attribute` - If true, this field will be rendered as attribute on the parent in xml, not as an element. (Default: false)
- `serialize_xml_inline` - If true, no collection wrapper element will be rendered for a collection of elements. If false, wrap all elements. (Default: true)
- `serialize_name` - Custom name of the element in serialized form if it should deviate from the default naming strategy of turning camel-case into underscore. (Default: false)
- `serialize_only` - If true the field will be removed from `FormView` and therefor only be present in the serialized data (json, xml) 

## Usage

This bundle defines a new service to serialize forms inside the Symfony DIC:

    <?php
    class UserController extends Controller
    {
        public function showAction(User $user)
        {
            $serializer = $this->get('form_serializer');
            $xml = $serializer->serialize($user, new UserType(), 'xml');

            return new Response($xml, 200, array('Content-Type' => 'text/xml'));
        }
    }

It also registers a Listener inside the form framework that binds XML and JSON requests
onto a form. Just call `$form->bind($request)` as shown in the example.

If you want to convert JMS Serializer based configuration to FormTypes you can use the command that is included:

    php app/console simplethings:convert-jms-metadata "className"

Since JMS Serializer automatically builds metadata for every class, you can use this command to generate form types for any existing class for you.

## Configuration

    simple_things_form_serializer:
        include_root_in_json: false
        application_xml_root_name: ~
        naming_strategy: camel_case

## Example

Take a usual form, extended with some details about serialization:

    <?php
    namespace Acme\DemoBundle\Form\Type;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class UserType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('username', 'text')
                ->add('email', 'email')
                ->add('country', 'entity')
                ->add('addresses', 'collection', array('type' => 'address', 'serialize_xml_name' => 'address'))
                ->add('created', 'datetime', array('read_only' => true))
            ;
        }

        public function getName()
        {
            return 'user';
        }

        public function setDefaultOptions(OptionsResolverInterface $options)
        {
            $options->setDefaults(array(
                'data_class' => 'Acme\DemoBundle\Entity\User',
                'serialize_xml_name' => 'user',
            ));
        }
    }

Using the serializer:

    <?php
    $serializer = $this->get('form_serializer');
    $data       = $serializer->serialize($user, new UserType(), 'xml');

Produces:

    <user>
        <username>beberlei</username>
        <email>kontakt@beberlei.de</email>
        <country>de</country>
        <addresses>
            <address>
                <street>Foostreet 1</street>
            </address>
        </addresses>
        <created>2012-07-10</created>
    </user>

Or if you use JSON:

    {
        "user": {
            "username": "beberlei",
            "email": "kontakt@beberlei.de",
            "country": "de",
            "addresses": [
                {"street": "Foostreet 1"}
            ],
            "created": "2012-07-10"
        }
    }

Deserializing will look familiar:

    class UserController extends Controller
    {
        public function editAction(Request $request)
        {
            $em = $this->get('doctrine.orm.default_entity_manager');

            $user = $em->find('Acme\DemoBundle\Entity\User', $request->get('id'));
            $form = $this->createForm(new UserType(), $user);

            if ($request->getMethod() !== 'POST') {
                return $this->renderFormFailure("MyBundle:User:edit.html.twig", $form, array('user' => $user));
            }

            $form->bind($request);

            if ( ! $form->isValid()) {
                return $this->renderFormFailure("MyBundle:User:edit.html.twig", $form, array('user' => $user));
            }

            // do some business logic here

            $em->flush();

            return $this->formRedirect($form, $this->generateUrl('user_show', array('id' => $user->getId()), 201);
        }

        /* either render the form errors as xml/json or the html form again based on " _format" */
        public function renderFormFailure($template, FormInterface $form, $parameters)
        {
        }

        /* redirect OR 201 created, based on the "_format" */
        public function formRedirect()
        {
        }
    }

This looks almost like a out of the book form request. The only thing different
is that we have to use the "renderFormView" and "formRedirect" methods to generate
response objects.

- `renderFormView` will decide based on the response format, what operation to perform.
    1. show a form, when format is html
    2. show a HTTP 405 error, when the passed form wasn't bound yet
    3. show a HTTP 412 pre-condition failed with the form errors serialized into xml or json.
- `formRedirect` will decide based on the response format:
    1. to redirect to the given url if its html (or config option `use_forwards = false`)
    2. to forward to the route if its xml or json (and config option `use_forwards = true`)

