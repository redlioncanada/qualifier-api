<?php

class Wpq_FeedProcessorController extends Zend_Controller_Action {

  public function init() {
    /* Initialize action controller here */
  }

  public function indexAction() {
    set_time_limit(0);
    $feed = new Wpq_Model_Feed();
    $feed->rebuildJson();
  }

}
