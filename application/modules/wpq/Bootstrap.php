<?php

use Lrr\ServiceLocator,
    Rlc\Wpq,
    Rlc\Wpq\FeedEntity;

/**
 * This class's mere existence is necessary for the module autoloader
 * to be automatically set up by Zend_Application.
 */
class Wpq_Bootstrap extends Zend_Application_Module_Bootstrap {

  protected function _initServices() {
    $serviceLocator = new ServiceLocator();
    ServiceLocator::load($serviceLocator);

    $dataPath = realpath(APPLICATION_PATH . '/../data'); // no trailing slash
    $xmlReader = new Wpq\XmlReader($dataPath);
    $jsonBuilder = new Wpq\JsonBuilder($xmlReader);
    $serviceLocator
            ->loadJsonFileManager(new Wpq\JsonFileManager($dataPath, $jsonBuilder))
            ->catalogEntryFactory(function(\SimpleXMLElement $el) {
              return new FeedEntity\CatalogEntry($el);
            })
            ->catalogGroupFactory(function () {
              return new FeedEntity\CatalogGroup('en_CA');
            })
    ;
  }

}
