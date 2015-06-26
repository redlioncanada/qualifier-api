<?php

use Lrr\ServiceLocator;

class Wpq_ProductListController extends Zend_Controller_Action {

  public function init() {
    $this->getHelper('ViewRenderer')->setNoRender();
  }

  public function indexAction() {
    $config = ServiceLocator::config();

    $brands = $config->brands->toArray();
    $brand = $this->getParam('brand');
    if (!in_array($brand, $brands)) {
      throw new Zend_Controller_Action_Exception("Invalid brand", 404);
    }

    $locales = $config->locales->toArray();
    $locale = $this->getParam('locale');
    if (!in_array($locale, $locales)) {
      throw new Zend_Controller_Action_Exception("Invalid locale", 404);
    }

    $jsonFileManager = ServiceLocator::jsonFileManager();
    $filename = $jsonFileManager->getJsonFilename($brand, $locale);
    if (!is_file($filename)) {
      throw new \Zend_Controller_Action_Exception("No JSON has been generated "
      . "for brand: $brand and locale: $locale", 404);
    }

    // In case a new version is being written, make sure to wait until
    // it's complete.
    $stream = fopen($filename, 'r');
    flock($stream, LOCK_SH);
    $fileContents = stream_get_contents($stream);
    flock($stream, LOCK_UN);
    fclose($stream);

    // TODO is there a way to set a stream to the body instead of a string?
    // would that be better?
    $this->getResponse()->setHeader('Content-type', 'application/json')
        ->setBody($fileContents);
  }

}
