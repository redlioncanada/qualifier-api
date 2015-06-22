<?php

/**
 * This class's mere existence is necessary for the module autoloader
 * to be automatically set up by Zend_Application.
 */
class Wpq_Bootstrap extends Zend_Application_Module_Bootstrap {

  protected function _initPlugins() {
    $application = $this->getApplication();
    $application->bootstrap('frontcontroller');
    $front = $application->getResource('frontcontroller');
    $front->registerPlugin(new Wpq_Plugin_ServicesLoader());
  }

}
