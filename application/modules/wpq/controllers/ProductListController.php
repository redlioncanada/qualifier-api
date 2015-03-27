<?php

class Wpq_ProductListController extends Zend_Controller_Action {

  public function init() {
    $this->getHelper('ViewRenderer')->setNoRender();
  }

  public function getAction() {
    $catSlug = $this->getParam('category');
    $cats = $this->getInvokeArg('bootstrap')->getOption('wpq')['categories'];
    if (!preg_match('/^[a-z0-9_-]+$/i', $catSlug)
            || !in_array($catSlug, $cats)) {
      throw new Zend_Controller_Action_Exception("Invalid category", 400);
    }

    $catModel = new Wpq_Model_Category($catSlug);
    $filename = $catModel->getJsonFilename();

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
