<?php

use Lrr\ServiceLocator;

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function indexAction() {
    set_time_limit(0);
    
    $config = ServiceLocator::config();
    $brands = $config->brands->toArray();
    $locales = $config->locales->toArray();
    $jsonFileManager = ServiceLocator::jsonFileManager();
    
    foreach ($brands as $brand) {
      foreach ($locales as $locale) {
        $jsonFileManager->rebuildJson($brand, $locale);
      }
    }
  }

}
