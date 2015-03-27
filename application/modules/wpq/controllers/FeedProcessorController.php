<?php

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function init() {
    /* Initialize action controller here */
  }

  public function indexAction() {
    set_time_limit(0);
    $cats = $this->getInvokeArg('bootstrap')->getOption('wpq')['categories'];
    foreach ($cats as $cat) {
      $catModel = new Wpq_Model_Category($cat);
      $catModel->rebuildJson();
    }
  }

}
