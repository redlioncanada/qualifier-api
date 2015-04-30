<?php

namespace Lrr;

class OptionSetter {

  /**
   * @var \Zend_Filter_Interface
   */
  private $methodNameInflector;

  public function __construct(\Zend_Filter_Interface $methodNameInflector) {
    $this->methodNameInflector = $methodNameInflector;
  }

  /**
   * @param array $options
   * @param object $object
   * @return \Cg\OptionSetter chainable
   */
  public function setOptions(array $options, $object) {
    foreach ($options as $key => $value) {
      $setterMethodName = 'set' . $this->methodNameInflector->filter($key);
      if (method_exists($object, $setterMethodName)) {
        $object->$setterMethodName($value);
      }
    }
    return $this;
  }

}
