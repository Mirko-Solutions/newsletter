<?php

namespace Mirko\Newsletter\MVC\View;

use ArrayAccess;
use DateTime;
use TYPO3\CMS\Extbase\Mvc\View\AbstractView;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\Exception\UnknownClassException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * A JSON view
 *
 * @scope prototype
 * @api
 */
class JsonView extends AbstractView
{
    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext
     */
    protected $controllerContext;

    /**
     * Only variables whose name is contained in this array will be rendered
     *
     * @var string[]
     */
    protected $variablesToRender = ['value'];

    /**
     * The rendering configuration for this JSON view which
     * determines which properties of each variable to render.
     *
     * The configuration array must have the following structure:
     *
     * Example 1:
     *
     * array(
     *        'variable1' => array(
     *            '_only' => array('property1', 'property2', ...)
     *        ),
     *        'variable2' => array(
     *            '_exclude' => array('property3', 'property4, ...)
     *        ),
     *        'variable3' => array(
     *            '_exclude' => array('secretTitle'),
     *            '_descend' => array(
     *                'customer' => array(
     *                    '_only' => array('firstName', 'lastName')
     *                )
     *            )
     *        ),
     *        'somearrayvalue' => array(
     *            '_descendAll' => array(
     *                '_only' => array('property1')
     *            )
     *        )
     * )
     *
     * Of variable1 only property1 and property2 will be included.
     * Of variable2 all properties except property3 and property4
     * are used.
     * Of variable3 all properties except secretTitle are included.
     *
     * If a property value is an array or object, it is not included
     * by default. If, however, such a property is listed in a "_descend"
     * section, the renderer will descend into this sub structure and
     * include all its properties (of the next level).
     *
     * The configuration of each property in "_descend" has the same syntax
     * like at the top level. Therefore - theoretically - infinitely nested
     * structures can be configured.
     *
     * To export indexed arrays the "_descendAll" section can be used to
     * include all array keys for the output. The configuration inside a
     * "_descendAll" will be applied to each array element.
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * @inject
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Injects the PersistenceManager.
     *
     * @param PersistenceManagerInterface $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Specifies which variables this JsonView should render
     * By default only the variable 'value' will be rendered
     *
     * @param string[] $variablesToRender
     *
     * @api
     */
    public function setVariablesToRender(array $variablesToRender)
    {
        $this->variablesToRender = $variablesToRender;
    }

    /**
     * @param array $configuration The rendering configuration for this JSON view
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Transforms the value view variable to a serializable
     * array represantion using a YAML view configuration and JSON encodes
     * the result.
     *
     * @return string The JSON encoded variables
     * @throws PropertyNotAccessibleException
     * @throws UnknownClassException
     * @api
     */
    public function render(): string
    {
        $propertiesToRender = $this->renderArray();

        return json_encode($propertiesToRender, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Loads the configuration and transforms the value to a serializable
     * array.
     *
     * @return array An array containing the values, ready to be JSON encoded
     * @throws PropertyNotAccessibleException
     * @throws UnknownClassException
     * @api
     */
    protected function renderArray()
    {
        if (count($this->variablesToRender) === 1) {
            $variableName = current($this->variablesToRender);
            $valueToRender = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;
            $configuration = isset($this->configuration[$variableName]) ? $this->configuration[$variableName] : [];
        } else {
            $valueToRender = [];
            foreach ($this->variablesToRender as $variableName) {
                $valueToRender[$variableName] = isset($this->variables[$variableName]) ? $this->variables[$variableName] : null;
            }
            $configuration = $this->configuration;
        }

        return $this->transformValue($valueToRender, $configuration);
    }

    /**
     * Transforms a value depending on type recursively using the
     * supplied configuration.
     *
     * @param mixed $value The value to transform
     * @param mixed $configuration Configuration for transforming the value or NULL
     *
     * @return array The transformed value
     * @throws PropertyNotAccessibleException
     * @throws UnknownClassException
     */
    protected function transformValue($value, $configuration)
    {
        if (is_array($value) || $value instanceof ArrayAccess) {
            $array = [];
            foreach ($value as $key => $element) {
                if (isset($configuration['_descendAll']) && is_array($configuration['_descendAll'])) {
                    $array[] = $this->transformValue($element, $configuration['_descendAll']);
                } else {
                    if (isset($configuration['_only']) && is_array($configuration['_only']) && !in_array($key, $configuration['_only'], true)) {
                        continue;
                    }
                    if (isset($configuration['_exclude']) && is_array($configuration['_exclude']) && in_array($key, $configuration['_exclude'], true)) {
                        continue;
                    }
                    $array[$key] = $this->transformValue($element, isset($configuration[$key]) ? $configuration[$key] : []);
                }
            }

            return $array;
        } elseif (is_object($value)) {
            return $this->transformObject($value, $configuration);
        }

        return $value;
    }

    /**
     * Traverses the given object structure in order to transform it into an
     * array structure.
     *
     * @param object $object Object to traverse
     * @param mixed $configuration Configuration for transforming the given object or NULL
     *
     * @return string Object structure as an array
     * @throws PropertyNotAccessibleException
     * @throws UnknownClassException
     */
    protected function transformObject($object, $configuration)
    {
        // hand over DateTime as ISO formatted string
        if ($object instanceof DateTime) {
            return $object->format('c');
        }
        // load LayzyLoadingProxy instances
        if ($object instanceof LazyLoadingProxy) {
            $object = $object->_loadRealInstance();
        }
        $propertyNames = ObjectAccess::getGettablePropertyNames($object);
        $propertiesToRender = [];
        foreach ($propertyNames as $propertyName) {
            if (isset($configuration['_only']) && is_array($configuration['_only']) && !in_array($propertyName, $configuration['_only'], true)) {
                continue;
            }
            if (isset($configuration['_exclude']) && is_array($configuration['_exclude']) && in_array($propertyName, $configuration['_exclude'], true)) {
                continue;
            }

            $propertyValue = ObjectAccess::getProperty($object, $propertyName);

            if (!is_array($propertyValue) && !is_object($propertyValue)) {
                $propertiesToRender[$propertyName] = $propertyValue;
            } elseif (isset($configuration['_descend']) && array_key_exists($propertyName, $configuration['_descend'])) {
                $propertiesToRender[$propertyName] = $this->transformValue($propertyValue, $configuration['_descend'][$propertyName]);
            }
        }
        if (isset($configuration['_exposeObjectIdentifier']) && $configuration['_exposeObjectIdentifier'] === true) {
            // we don't use the IdentityMap like its done in FLOW3 because there are some cases objects are not registered there.
            // TODO: rethink this solution - it is really quick and dirty...
            $propertiesToRender['__identity'] = $object->getUid();
        }

        return $propertiesToRender;
    }
}
