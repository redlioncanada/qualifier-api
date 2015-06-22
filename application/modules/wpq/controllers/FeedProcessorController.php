<?php

use Lrr\ServiceLocator;

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function indexAction() {
    set_time_limit(0);
    
    $brands = ServiceLocator::config()->brands->toArray();
    $jsonFileManager = ServiceLocator::jsonFileManager();
    
    foreach ($brands as $brand) {
      $jsonFileManager->rebuildJson($brand);
    }
  }

}
