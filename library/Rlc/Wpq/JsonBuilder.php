<?php

namespace Rlc\Wpq;

use Lrr\ServiceLocator;

class JsonBuilder {

  /**
   * @var XmlReader
   */
  private $xmlReader;

  function __construct(XmlReader $xmlReader) {
    $this->xmlReader = $xmlReader;
  }

  public function build() {
    $catalogEntries = $this->getCatalogEntries();

    // Just testing code here, will actually return all entries in all groups.
    // For now I filter for an arbitrary group.
    $targetGroupName = 'MC_Laundry_Laundry_Appliances_Laundry_Pairs_HighEfficiency_Front_Load';
    
    foreach ($catalogEntries as $key => $entry) {
      $anscestorCatalogGroups = $entry->getAllCatalogGroups();
      $anscestorCatalogGroupIds = array_map([$this, 'getGroupId'], $anscestorCatalogGroups);
      if (!in_array($targetGroupName, $anscestorCatalogGroupIds)) {
        unset($catalogEntries[$key]);
      }
    }

    // Just testing output
    ini_set('xdebug.var_display_max_depth', 5);
    var_dump($catalogEntries);
    die;

//    $json = json_encode($catalogEntries, JSON_PRETTY_PRINT);
//    return $json;
  }

  // Probably just for testing too
  private function getGroupId(FeedEntity\CatalogGroup $group) {
    return (string) $group->identifier;
  }

  /**
   * Get all catalog entries with all associated objects filled in
   * 
   * @return FeedEntity\CatalogEntry[]
   */
  private function getCatalogEntries() {
    // Fetch & assemble data for associations
    $entryData = $this->xmlReader->readFile('CatalogEntry_Full');
    $entryGroupRelnData = $this->xmlReader->readFile('CatalogGroupCatalogEntryRelationship_Full');
    $groups = $this->getCatalogGroups();

    /*
     * Build final entries array
     */

    // Start with just entries
    $entries = [];
    foreach ($entryData->record as $entryRecord) {
      $entries[(string) $entryRecord->partnumber] = ServiceLocator::catalogEntry($entryRecord);
    }

    // Scan through group assocs and assign them
    foreach ($entryGroupRelnData as $entryGroupRelnRecord) {
      $relnPartNumber = (string) $entryGroupRelnRecord->partnumber;
      $relnGroupId = (string) $entryGroupRelnRecord->catgroup_identifier;
      if (isset($entries[$relnPartNumber], $groups[$relnGroupId])) {
        $entries[$relnPartNumber]->addCatalogGroup($groups[$relnGroupId]);
      }
    }

    // TODO other assocs...

    return $entries;
  }

  /**
   * Get all catalog groups w parent/child assocs
   * 
   * @return FeedEntity\CatalogGroup[]
   */
  private function getCatalogGroups() {
    $groupData = $this->xmlReader->readFile('CatalogGroup_Full');
    $groupRelnData = $this->xmlReader->readFile('CatalogGroupRelationship_Full');

    /*
     * Init group objects
     */
    // Keyed by identifier
    $groups = [];
    foreach ($groupData->record as $groupRecord) {
      $groupId = (string) $groupRecord->identifier;
      if (!isset($groups[$groupId])) {
        $groups[$groupId] = ServiceLocator::catalogGroup();
      }
      $groups[$groupId]->initRecord($groupRecord, (string) $groupRecord->locale);
    }
    // I now have all groups with both locales filled in, even if they weren't
    // consecutive in the feed.

    /*
     * Assign parent-/child-group relationships
     */
    foreach ($groupRelnData as $relnRecord) {
      // (Top-level cats will have a blank catgroup_parent_identifier)
      if ('' != $relnRecord->catgroup_parent_identifier && '' != $relnRecord->catgroup_child_identifier) {
        $parent = $groups[(string) $relnRecord->catgroup_parent_identifier];
        $child = $groups[(string) $relnRecord->catgroup_child_identifier];
        $child->setParent($parent);
        $parent->addChild($child);
      }
    }
    unset($parent, $child); // remove unneeded references

    return $groups;
  }

}
