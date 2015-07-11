<?php

use Lrr\ServiceLocator;

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function indexAction() {
    set_time_limit(0);

    $config = ServiceLocator::config();
    $brands = $config->brands->toArray();
    $locales = $config->locales->toArray();
    $jsonFileManager = ServiceLocator::jsonFileManager();

    $this->getHelper('ViewRenderer')->setNoRender(true);
    $layout = Zend_Layout::getMvcInstance();
    if ($layout instanceof Zend_Layout) {
      $layout->disableLayout();
    }

    foreach ($brands as $brand) {
      foreach ($locales as $locale) {
        $jsonFileManager->rebuildJson($brand, $locale);
        if ('development' == APPLICATION_ENV) {
          echo "$brand, $locale<br>";
        }
      }
    }
  }

}
