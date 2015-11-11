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
   * @param string $brand
   * @param string $locale
   * @return string Full path
   */
  public function getJsonFilename($brand, $locale) {
    $path = $this->dataPath . '/' . $brand . '-' . $locale . '.json';
    return $path;
  }

  /**
   * @param string $brand
   * @param string $locale
   * @return void
   */
  public function rebuildJson($brand, $locale) {
    $filePath = $this->getJsonFilename($brand, $locale);
    $json = $this->buildJson($brand, $locale);
    file_put_contents($filePath, $json, LOCK_EX);
  }

  /**
   * @param string $brand
   * @param string $locale
   * @return string JSON
   */
  private function buildJson($brand, $locale) {
    $json = $this->jsonBuilder->build($brand, $locale);
    return $json;
  }
  
  
  /**
   * Client code calls this after processing all locales for a brand, to let
   * JsonBuilder know it can free memory allocated to the feedModelCache for
   * that brand.
   * 
   * @param string $brand
   * @return void
   */
  public function doneWith($brand) {
    $this->jsonBuilder->doneWith($brand);
  }

}
