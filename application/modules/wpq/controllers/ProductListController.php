<?php

use Lrr\ServiceLocator;

class Wpq_ProductListController extends Zend_Controller_Action {

  public function init() {
    $this->getHelper('ViewRenderer')->setNoRender();
  }

  public function indexAction() {
    $brands = ServiceLocator::config()->brands->toArray();
    $brand = $this->getParam('brand');
    if (!in_array($brand, $brands)) {
      throw new Zend_Controller_Action_Exception("Invalid brand", 404);
    }
    
    $jsonFileManager = ServiceLocator::jsonFileManager();
    $filename = $jsonFileManager->getJsonFilename($brand);

    // In case a new version is being written, make sure to wait until
    // it's complete.
    $stream = fopen($filename, 'r');
    flock($stream, LOCK_SH);
    $fileContents = stream_get_contents($stream);
    flock($stream, LOCK_UN);
    fclose($stream);

    $this->getResponse()->setHeader('Content-type', 'application/json')
            ->setBody($fileContents);
  }

}
