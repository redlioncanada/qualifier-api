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
   * @return string Full path
   */
  public function getJsonFilename($brand) {
    $path = realpath($this->dataPath . '/' . $brand . '.json');
    if (is_null($path)) {
      throw new \InvalidArgumentException("No JSON file exists for brand: $brand");
    }
    return $path;
  }

  /**
   * @param string $brand
   * @return void
   */
  public function rebuildJson($brand) {
    $json = $this->buildJson($brand);
    $filePath = $this->getJsonFilename($brand);
    file_put_contents($filePath, $json, LOCK_EX);
  }

  /**
   * @param string $brand
   * @return string JSON
   */
  private function buildJson($brand) {
    $json = $this->jsonBuilder->build($brand);
    return $json;
  }

}
