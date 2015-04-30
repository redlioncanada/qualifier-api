<?php

namespace Rlc\Wpq;

class JsonFileManager {

  /**
   * @var string
   */
  private $dataPath;

  /**
   * @var Wpq_Model_JsonBuilder
   */
  private $jsonBuilder;

  function __construct($dataPath, JsonBuilder $jsonBuilder) {
    $this->dataPath = $dataPath;
    $this->jsonBuilder = $jsonBuilder;
  }

  /**
   * @return string Filename
   */
  public function getJsonFilename() {
    return $this->dataPath . '/product-list.json';
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
    $json = $this->jsonBuilder->build();
    return $json;
  }

}
