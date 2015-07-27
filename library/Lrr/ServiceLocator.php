<?php

namespace Lrr;

/**
 * Instantiate and load with instances by calling instance methods of the form
 * `$serviceLocatorInstance->loadServiceName($serviceInstance)`.
 * 
 * You can also define factories (so only unique instances are returned) by
 * calling instance methods of the form
 * `$serviceLocatorInstance->serviceNameFactory(function ([$arg1, ...]) { ... })`.
 * 
 * Then load singleton with `ServiceLocator::load($serviceLocatorInstance)`,
 * and retrieve instances elsewhere with `ServiceLocator::serviceName()`. This
 * will first look for a matching service (ready-made object) with a matching
 * name, then look for a matching factory, and use it to build a new object and
 * return it. For factories, any arguments passed to ::serviceName(...) will be
 * passed as arguments to the factory.
 * 
 * @author Linus Rachlis <linus@rachlis.net>
 */
class ServiceLocator {

  /**
   * @var ServiceLocator
   */
  private static $soleInstance;

  /**
   * @var stdClass[]
   */
  private $services = [];

  /**
   * @var Closure[]
   */
  private $factories = [];

  public static function load(ServiceLocator $serviceLocator) {
    self::$soleInstance = $serviceLocator;
  }

  public static function __callStatic($name, $arguments) {
    $regKey = ucfirst($name);
    if (isset(self::$soleInstance->services[$regKey])) {
      return self::$soleInstance->services[$regKey];
    } elseif (isset(self::$soleInstance->factories[$regKey])) {
      return call_user_func_array(self::$soleInstance->factories[$regKey], $arguments);
    } else {
      throw new \Exception("No service or factory registered for '$regKey'");
    }
  }

  public function __call($name, $arguments) {
    if (substr($name, 0, 4) === 'load') {
      $regKey = ucfirst(substr($name, 4));
      $service = $arguments[0];
      $this->services[$regKey] = $service;
      return $this;
    } elseif (substr($name, -7) === 'Factory') {
      $regKey = ucfirst(substr($name, 0, -7));
      $factory = $arguments[0];
      if (gettype($factory) !== 'object' || !($factory instanceof \Closure)) {
        throw new \Exception("Argument to xxxFactory() must be a Closure object");
      }
      $this->factories[$regKey] = $factory;
      return $this;
    } else {
      throw new \Exception("Method not implemented: $name");
    }
  }

}
