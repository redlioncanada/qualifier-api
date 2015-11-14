<?php

use Lrr\ServiceLocator;

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function indexAction() {
    if ('development' != APPLICATION_ENV) {
      // Don't stop if the connection drops
      ignore_user_abort(true);
    }
    // Set 30 minutes for sanity - this should only be reached if something
    // bad happens and we don't want to tie up resources forever.
    set_time_limit(30 * 60);

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
          echo $jsonFileManager->getJsonFilename($brand, $locale) . "<br>";
        }
      }
    }
  }

}
