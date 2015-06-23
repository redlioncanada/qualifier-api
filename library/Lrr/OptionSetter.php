<?php

namespace Lrr;

/**
 * Encapsulate the "call setter methods corresponding to array keys" pattern.
 * 
 * Example:
 * <code>
 * // Setup:
 * $filter = new Zend_Filter_Word_UnderscoreToCamelCase();
 * $serviceLocator->loadOptionSetter(new \Lrr\OptionSetter($filter));
 * 
 * // Usage:
 * $obj = new SomeClass();
 * $options = somehow get array...
 * ServiceLocator::optionSetter()->setOptions($options, $obj);
 * </code>
 * 
 * @author Linus Rachlis <linus@rachlis.net>
 */
class OptionSetter {

  /**
   * @var \Zend_Filter_Interface
   */
  private $methodNameInflector;

  /**
   * @param \Zend_Filter_Interface $methodNameInflector OPTIONAL (if not given, no inflection)
   */
  public function __construct(\Zend_Filter_Interface $methodNameInflector = null) {
    $this->methodNameInflector = $methodNameInflector;
  }

  /**
   * @param array $options
   * @param object $object
   * @return \Cg\OptionSetter chainable
   */
  public function setOptions(array $options, $object) {
    foreach ($options as $key => $value) {
      $inflected = isset($this->methodNameInflector) ? $this->methodNameInflector->filter($key) : $key;
      $setterMethodName = 'set' . $inflected;
      if (method_exists($object, $setterMethodName)) {
        $object->$setterMethodName($value);
      }
    }
    return $this;
  }

}
