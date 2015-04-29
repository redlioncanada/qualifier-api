<?php

class Wpq_Model_Feed {

  /**
   * @var string
   */
  private $dataPath;

  function __construct() {
    $this->dataPath = realpath(APPLICATION_PATH . '/../data/');
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
    // Get groups (categories) first
    $groupsToParts = $this->getGroupsToParts();
    var_dump($groupsToParts);die;
    $catalogEntry = $this->getXmlFromFile('CatalogEntry_Full');
    $json = json_encode((array) $catalogEntry);
    return $json;
  }
  
  private function getGroupsToParts() {
    $relData = $this->getXmlFromFile('CatalogGroupCatalogEntryRelationship_Full');
    $groupsToParts = [];
    foreach ($relData->record as $r) {
      $groupsToParts[(string) $r->catgroup_identifier][] = (string) $r->partnumber;
    }
    return $groupsToParts;
  }

  private function getXmlFromFile($file) {
    return simplexml_load_file($this->dataPath . '/MTG_CA_' . $file . '.xml');
  }

}
