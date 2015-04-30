<?php

namespace Rlc\Wpq;

class JsonBuilder {

  /**
   * @var XmlReader
   */
  private $xmlReader;

  function __construct(XmlReader $xmlReader) {
    $this->xmlReader = $xmlReader;
  }

  public function build() {
    // Get groups (categories) first
    $groupsToParts = $this->getGroupsToParts();
    var_dump($groupsToParts);
    die;
    $catalogEntry = $this->xmlReader->getXmlFromFile('CatalogEntry_Full');
    $json = json_encode((array) $catalogEntry);
    return $json;
  }

  private function getGroupsToParts() {
    $relData = $this->xmlReader->getXmlFromFile('CatalogGroupCatalogEntryRelationship_Full');
    $groupsToParts = [];
    foreach ($relData->record as $r) {
      $groupsToParts[(string) $r->catgroup_identifier][] = (string) $r->partnumber;
    }
    return $groupsToParts;
  }

}
