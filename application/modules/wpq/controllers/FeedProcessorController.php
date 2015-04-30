<?php

use Lrr\ServiceLocator;

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function init() {
    /* Initialize action controller here */
  }

  public function indexAction() {
    set_time_limit(0);
    $jsonFileManager = ServiceLocator::jsonFileManager();
    $jsonFileManager->rebuildJson();
  }

}
