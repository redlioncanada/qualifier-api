<?php

namespace Rlc\Wpq;

use Lrr\ServiceLocator;

class JsonBuilder {

  /**
   * @var XmlReaderInterface
   */
  private $xmlReader;

  function __construct(XmlReaderInterface $xmlReader) {
    $this->xmlReader = $xmlReader;
  }

  public function build() {
    $catalogEntries = $this->getCatalogEntries();

    // Just testing code here, will actually return all entries in all groups.
    // For now I filter for an arbitrary group.
    $targetGroupId = 'SC_Kitchen_Cooking_Hoods_Under_Cabinet';

    $getGroupId = function ($group) {
      return (string) $group->identifier;
    };

    // Filter for target group - dev only to speed up testing
    foreach ($catalogEntries as $key => $entry) {
      $allCatalogGroups = $entry->getAllCatalogGroups();
      $allCatalogGroupIds = array_map($getGroupId, $allCatalogGroups);
      if (!in_array($targetGroupId, $allCatalogGroupIds)) {
        unset($catalogEntries[$key]);
      }
    }

    // Build output data - beginning of real production code
    $outputData = [];
    foreach ($catalogEntries as $entry) {
      $allCatalogGroups = $entry->getAllCatalogGroups();
      $allCatalogGroupIds = array_map($getGroupId, $allCatalogGroups);
      $newOutputData = [
        'sku' => (string) $entry->partnumber,
        'groups' => $allCatalogGroupIds,
        'colours' => [],
      ];
      
      $childEntries = $entry->getChildEntries();
      foreach ($childEntries as $childEntry) {
        $variantPartNumber = (string) $childEntry->partnumber;
        $colourDa = $childEntry->getDefiningAttributeValue('Color');
        $newOutputData['colours'][] = [
          'sku' => $variantPartNumber,
          'colourCode' => (string) $colourDa->valueidentifier,
          'colourName' => (string) $colourDa->value,
        ];
      }
      
      $outputData[] = $newOutputData;
    }
//    
    // Just testing output
//    ini_set('xdebug.var_display_max_depth', 5);
//    var_dump($catalogEntries['MEW6527DDQ']->getParentEntry());
//    die;

    $json = json_encode($outputData, JSON_PRETTY_PRINT);
//    die($json);
    return $json;
  }

  /**
   * Get all catalog entries with all associated objects filled in
   * 
   * @return FeedEntity\CatalogEntry[]
   */
  private function getCatalogEntries() {
    // Fetch & assemble data for associations
    $entryData = $this->xmlReader->readFile('CatalogEntry');
    $entryGroupRelnData = $this->xmlReader->readFile('B2C_CatalogGroupCatalogEntryRelationship');
    $groups = $this->getCatalogGroups();
    $entryDescriptionData = $this->xmlReader->readFile('CatalogEntryDescription');

    /*
     * Build array in steps. We're working toward an array of top-level products
     * (catalog entries) only, with child products (colour variants), categories,
     * and all other associations assigned and retrievable using the classes'
     * accessor methods.
     */

    // Start by creating two arrays, one just stores all catalog entries by
    // partnumber, and the other stores only top-level entries by partnumber.
    // It's this 2nd array we'll ultimately return.
    $entries = $topLevelEntries = [];
    foreach ($entryData->record as $entryRecord) {
      $newEntry = ServiceLocator::catalogEntry($entryRecord);
      $sPartNumber = (string) $entryRecord->partnumber;
      $entries[$sPartNumber] = $newEntry;
      $sParentPartNumber = (string) $entryRecord->parentpartnumber;
      if ('' === $sParentPartNumber) {
        $topLevelEntries[$sPartNumber] = $newEntry;
      }
    }

    // Assign parent entry to all child entries via parentpartnumber field.
    // (This has to be a separate loop from above, because all entries need to
    // be indexed first.)
    foreach ($entryData->record as $entryRecord) {
      $sParentPartNumber = (string) $entryRecord->parentpartnumber;
      if ('' !== $sParentPartNumber) {
        $sPartNumber = (string) $entryRecord->partnumber;
        // Use $topLevelEntries to look up parents as an optimisation -- it's shorter.
        $entries[$sPartNumber]->setParentEntry($topLevelEntries[$sParentPartNumber]);
        $topLevelEntries[$sParentPartNumber]->addChildEntry($entries[$sPartNumber]);
      }
    }

    // Scan through group assocs and assign them to entries
    foreach ($entryGroupRelnData->record as $entryGroupRelnRecord) {
      $relnPartNumber = (string) $entryGroupRelnRecord->partnumber;
      $relnGroupId = (string) $entryGroupRelnRecord->catgroup_identifier;
      if (isset($entries[$relnPartNumber], $groups[$relnGroupId])) {
        $entries[$relnPartNumber]->addCatalogGroup($groups[$relnGroupId]);
      }
    }

    // Assign entry descriptions
    // Note: entry descriptions exist for both top-level and child part numbers,
    // but may be redundant.
    foreach ($entryDescriptionData->record as $entryDescriptionRecord) {
      $descriptionPartNumber = (string) $entryDescriptionRecord->partnumber;
      if (isset($entries[$descriptionPartNumber])) {
        $entryDescription = ServiceLocator::catalogEntryDescription($entryDescriptionRecord);
        $entries[$descriptionPartNumber]->addCatalogEntryDescription($entryDescription);
      }
    }
    
    $this->assignDefiningAttributeValues($entries);
    $this->assignDescriptiveAttributes($entries);

    return $topLevelEntries;
  }
  
  /**
   * @param FeedEntity\CatalogEntry[] $entries
   * @return void
   */
  private function assignDefiningAttributeValues(array &$entries) {
    // Assign defining attribute values (no use attaching defining attributes,
    // the data in the definingattributevalue file are enough).
    // Note, these only exist for child entries (colour variants).
    $definingAttributeValueData = $this->xmlReader->readFile('DefiningAttributeValue');
    foreach ($definingAttributeValueData->record as $definingAttributeValueRecord) {
      $davPartNumber = (string) $definingAttributeValueRecord->partnumber;
      if (isset($entries[$davPartNumber])) {
        // Check if already created and added, and do so if not
        $definingAttributeName = (string) $definingAttributeValueRecord->attributename;
        $definingAttributeValue = $entries[$davPartNumber]
            ->getDefiningAttributeValue($definingAttributeName);
        if (is_null($definingAttributeValue)) {
          $definingAttributeValue = ServiceLocator::definingAttributeValue();
          $entries[$davPartNumber]->addDefiningAttributeValue($definingAttributeValue, $definingAttributeName);
        }
        // Now we have a reference to the DAV for the given attribute name for
        // the given catalog entry, whether it already existed or was just
        // created. It's a compound record obj. Add the record for the locale
        // value we have in the current loop iteration.
        $definingAttributeValue->initRecord($definingAttributeValueRecord, (string) $definingAttributeValueRecord->locale);
      }
    }
  }

  /**
   * @param FeedEntity\CatalogEntry[] $entries
   * @return void
   */
  private function assignDescriptiveAttributes(array &$entries) {
    // Assign descriptive attributes.
    // Note, these only exist for top-level entries, so we optimize by using
    // $topLevelEntries for lookup, since it contains references to the same
    // objects, but is shorter.
    $descriptiveAttributeData = $this->xmlReader->readFile('DescriptiveAttribute');
    foreach ($descriptiveAttributeData->record as $descriptiveAttributeRecord) {
      $daPartNumber = (string) $descriptiveAttributeRecord->partnumber;
      if (isset($entries[$daPartNumber])) {
        // Check if already created and added, and do so if not
        $descriptiveAttributeGroupName = (string) $descriptiveAttributeRecord->groupname;
        $descriptiveAttributeGroup = $entries[$daPartNumber]
            ->getDescriptiveAttributeGroup($descriptiveAttributeGroupName);
        if (is_null($descriptiveAttributeGroup)) {
          $descriptiveAttributeGroup = ServiceLocator::descriptiveAttributeGroup();
          $entries[$daPartNumber]->addDescriptiveAttributeGroup($descriptiveAttributeGroup, $descriptiveAttributeGroupName);
        }
        // Now we have a reference to the DAG for the given groupname for
        // the given catalog entry, whether it already existed or was just
        // created.
        $descriptiveAttributeGroup->loadRecord($descriptiveAttributeRecord);
      }
    }
  }
  
  /**
   * Get all catalog groups w parent/child assocs
   * 
   * @return FeedEntity\CatalogGroup[]
   */
  private function getCatalogGroups() {
    $groupData = $this->xmlReader->readFile('B2C_CatalogGroup');
    $groupRelnData = $this->xmlReader->readFile('B2C_CatalogGroupRelationship');

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
