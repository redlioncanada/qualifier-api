<?php

class Wpq_ProductListController extends Zend_Controller_Action {

  public function init() {
    $this->getHelper('ViewRenderer')->setNoRender();
  }

  public function getAction() {
    $feedModel = new Wpq_Model_Feed();
    $filename = $feedModel->getJsonFilename();

    if (!is_file($filename)) {
      throw new Zend_Controller_Action_Exception("JSON file not found", 404);
    }

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
