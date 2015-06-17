<?php

namespace Rlc\Wpq\FeedEntity;

/**
 * <record> from CatalogEntry_Full file.
 */
class CatalogEntry extends AbstractSimpleRecord {

  /**
   * Keyed by locale
   * 
   * @var CatalogEntryDescription[]
   */
  private $catalogEntryDescriptions = [];

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
   * Gets all catalog groups including their parent groups, recursively,
   * in one flat array.
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

  /**
   * @param string $locale
   * @return CatalogEntryDescription
   */
  public function getCatalogEntryDescription($locale) {
    return $this->catalogEntryDescriptions[$locale];
  }

  /**
   * @param string $name
   * @return DescriptiveAttributeGroup
   */
  public function getDescriptiveAttributeGroup($name) {
    return isset($this->descriptiveAttributeGroups[$name]) ? $this->descriptiveAttributeGroups[$name] : null;
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

  public function addCatalogEntryDescription(CatalogEntryDescription $catalogEntryDescription) {
    $this->catalogEntryDescriptions[(string) $catalogEntryDescription->locale] = $catalogEntryDescription;
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

}
