<?php

namespace Rlc\Wpq\FeedEntity;

/**
 * <record> from CatalogEntry_Full file.
 */
class CatalogEntry extends AbstractSimpleRecord {

  /**
   * @var CatalogEntryDescription
   */
  private $catalogEntryDescription;

  /**
   * @var CatalogGroup
   */
  private $catalogGroups = [];

  /**
   * Keyed by groupname
   * 
   * @var DescriptiveAttributeGroup[]
   */
  private $descriptiveAttributeGroups = [];

  /**
   * @var DefiningAttributeValue[]
   */
  private $definingAttributeValues = [];

  /**
   * Optional entry corresponding to <parentpartnumber/> value.
   * 
   * @var CatalogEntry
   */
  private $parentEntry;

  /**
   * Optional array corresponding to entries whose <parentpartnumber/> values
   * reference this entry.
   * 
   * @var CatalogEntry[]
   */
  private $childEntries = [];

  /**
   * Optional array of associated records in the B2C_Price file. N.B. they may
   * have various currencies, start/enddates, and published 1/0 flags. See
   * comment in Wpq\FeedEntity\Price.
   * 
   * N.B. only child products (colour variants) have associated price data.
   * 
   * @var Price[]
   */
  private $prices = [];

  /**
   * Gets all catalog groups including their parent groups, recursively,
   * in one flat array.
   * 
   * @return CatalogGroup[]
   */
  public function getAllCatalogGroups() {
    $directGroups = $this->getCatalogGroups();
    $allGroups = $directGroups;
    foreach ($directGroups as $directGroup) {
      $allGroups = array_merge($allGroups, $directGroup->getAncestors());
    }
    return $allGroups;
  }

  public function getCatalogGroups() {
    return $this->catalogGroups;
  }
  
  public function isInGroupId($groupId) {
    $allGroups = $this->getAllCatalogGroups();
    foreach ($allGroups as $group) {
      if ($group->identifier == $groupId) {
        return true;
      }
    }
    return false;
  }

  /**
   * @return CatalogEntryDescription or NULL if none set
   */
  public function getDescription() {
    return $this->catalogEntryDescription;
  }

  /**
   * @param string $name
   * @return DescriptiveAttributeGroup
   */
  public function getDescriptiveAttributeGroup($name) {
    return isset($this->descriptiveAttributeGroups[$name]) ? $this->descriptiveAttributeGroups[$name] : null;
  }
  
  public function getDescriptiveAttributeGroups() {
    return $this->descriptiveAttributeGroups;
  }

  /**
   * @return DefiningAttributeValue[]
   */
  public function getDefiningAttributeValues() {
    return $this->definingAttributeValues;
  }

  /**
   * @param string $attributeName
   * @return DefiningAttributeValue
   */
  public function getDefiningAttributeValue($attributeName) {
    return isset($this->definingAttributeValues[$attributeName]) ? $this->definingAttributeValues[$attributeName] : null;
  }

  public function addCatalogGroup(CatalogGroup $catalogGroup) {
    $this->catalogGroups[] = $catalogGroup;
    return $this;
  }

  public function setDescription(CatalogEntryDescription $catalogEntryDescription) {
    $this->catalogEntryDescription = $catalogEntryDescription;
    return $this;
  }

  /**
   * 
   * @param DescriptiveAttributeGroup $descriptiveAttributeGroup
   * @param string $name
   */
  public function addDescriptiveAttributeGroup(DescriptiveAttributeGroup $descriptiveAttributeGroup,
      $name) {
    $this->descriptiveAttributeGroups[$name] = $descriptiveAttributeGroup;
    return $this;
  }

  public function addDefiningAttributeValue(DefiningAttributeValue $definingAttributeValue,
      $attributeName) {
    $this->definingAttributeValues[$attributeName] = $definingAttributeValue;
    return $this;
  }

  /**
   * @return CatalogEntry
   */
  public function getParentEntry() {
    return $this->parentEntry;
  }

  /**
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $parentEntry
   * @return CatalogEntry self
   */
  public function setParentEntry(CatalogEntry $parentEntry) {
    $this->parentEntry = $parentEntry;
    return $this;
  }

  /**
   * @return CatalogEntry[]
   */
  public function getChildEntries() {
    return $this->childEntries;
  }

  /**
   * @param CatalogEntry $childEntry
   * @return \Rlc\Wpq\FeedEntity\CatalogEntry self
   */
  public function addChildEntry(CatalogEntry $childEntry) {
    $this->childEntries[] = $childEntry;
    return $this;
  }

  /**
   * @param \Rlc\Wpq\FeedEntity\Price $price
   * @return \Rlc\Wpq\FeedEntity\CatalogEntry
   */
  public function addPrice(Price $price) {
    $this->prices[] = $price;
    return $this;
  }

  /**
   * N.B. only child products (colour variants) have associated price data.
   * 
   * @return Price[]
   */
  public function getPrices() {
    return $this->prices;
  }

  public function isTopLevel() {
    return !$this->parentpartnumber;
  }
  
}
