<?php

namespace Rlc\Wpq\FeedEntity;

use Rlc\Wpq\FeedEntity;

/**
 * <record> from CatalogEntry_Full file.
 */
class CatalogEntry extends AbstractSimple {

  private $catalogGroups = [];
  private $catalogEntryDescriptions = [];
  private $descriptiveAttributes = [];
  private $definingAttributes = [];

  // TODO Have this here or place under definingAttributes?
//  private $definingAttributeValues = [];

  /**
   * NB: probably won't be necessary after testing
   * 
   * Gets all catalog groups including their parent groups, recursively,
   * in one flat array.
   */
  public function getAllCatalogGroups() {
    $directGroups = $this->getCatalogGroups();
    $allGroups = $directGroups;
    foreach ($directGroups as $directGroup) {
      $allGroups += $directGroup->getAncestors();
    }
    return $allGroups;
  }

  public function getCatalogGroups() {
    return $this->catalogGroups;
  }

  public function getCatalogEntryDescription($locale) {
    return $this->catalogEntryDescription[$locale];
  }

  public function getDescriptiveAttributes() {
    return $this->descriptiveAttributes;
  }

  public function getDefiningAttributes() {
    return $this->definingAttributes;
  }

  public function addCatalogGroup($catalogGroup) {
    $this->catalogGroups[] = $catalogGroup;
  }

  public function addCatalogEntryDescription($catalogEntryDescription) {
    $this->catalogEntryDescriptions[(string) $catalogEntryDescription->locale] = $catalogEntryDescription;
  }

  public function addDescriptiveAttribute($descriptiveAttribute) {
    $this->descriptiveAttributes[] = $descriptiveAttribute;
  }

  public function addDefiningAttribute($definingAttribute) {
    $this->definingAttributes[] = $definingAttribute;
  }

}
