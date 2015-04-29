<?php

class Wpq_Model_Category {

  /**
   * @var string
   */
  private $jsonPath;

  /**
   * @var string
   */
  private $xmlPath;

  /**
   * @var string
   */
  private $catSlug;

  function __construct($catSlug) {
    $this->catSlug = $catSlug;
    $this->jsonPath = realpath(APPLICATION_PATH . '/../data/json');

    // Will change, need to be extracted, etc.
    $this->xmlPath = realpath(APPLICATION_PATH . '/../data/xml');
  }

  /**
   * @return string Filename
   */
  public function getJsonFilename() {
    return $this->jsonPath . '/' . $this->catSlug . '.json';
  }

  /**
   * @return void
   */
  public function rebuildJson() {
    $json = $this->buildJson();
    file_put_contents($this->getJsonFilename(), $json, LOCK_EX);
  }

  /**
   * @return string JSON
   */
  private function buildJson() {
    $xmlDoc = $this->getXmlDoc();
    $json = json_encode((array) $xmlDoc);
    return $json;
  }

  private function getXmlDoc() {
    return simplexml_load_file($this->getXmlFilename());
  }

  private function getXmlFilename() {
    // TODO will actually be synthesized from many files
    return $this->xmlPath . '/' . $this->catSlug . '.xml';
  }

}
