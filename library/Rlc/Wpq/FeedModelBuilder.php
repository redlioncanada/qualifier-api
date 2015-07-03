<?php

namespace Rlc\Wpq;

use Lrr\ServiceLocator;

/**
 * Extracts all info from XML files and constructs a web of associated value
 * objects to make life easy for JsonBuilder.
 * 
 * @todo cache results in member var
 */
class FeedModelBuilder implements FeedModelBuilderInterface {

  /**
   * @var XmlReaderInterface
   */
  private $xmlReader;

  function __construct(XmlReaderInterface $xmlReader) {
    $this->xmlReader = $xmlReader;
  }

  /**
   * Get top-level catalog entries with all associated objects filled in
   * 
   * @param string $brand
   * @param array  $filterForGroups OPTIONAL only return products that are in
   *                                one of these groups.
   * @return FeedEntity\CatalogEntry[]
   */
  public function buildFeedModel($brand, $filterForGroups = []) {
    // Fetch data for associations
    $entryData = $this->xmlReader->readFile($brand, 'CatalogEntry');
    $entryGroupRelnData = $this->xmlReader->readFile($brand, 'B2C_CatalogGroupCatalogEntryRelationship');
    $groups = $this->getCatalogGroups($brand);
    $priceData = $this->xmlReader->readFile($brand, 'B2C_Price');

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

    // Scan through group assocs and assign them to entries
    foreach ($entryGroupRelnData->record as $entryGroupRelnRecord) {
      $relnPartNumber = (string) $entryGroupRelnRecord->partnumber;
      $relnGroupId = (string) $entryGroupRelnRecord->catgroup_identifier;
      if (isset($entries[$relnPartNumber], $groups[$relnGroupId])) {
        $entries[$relnPartNumber]->addCatalogGroup($groups[$relnGroupId]);
      }
    }

    if (count($filterForGroups)) {
      // Do group filtering here as an optimisation
      $getGroupId = function ($group) {
        return (string) $group->identifier;
      };
      foreach ($topLevelEntries as $sku => $entry) {
        $allCatalogGroups = $entry->getAllCatalogGroups();
        $allCatalogGroupIds = array_map($getGroupId, $allCatalogGroups);
        // If none of the groups is one of the target groups
        if (!count(array_intersect($filterForGroups, $allCatalogGroupIds))) {
          // Get rid of that product - in both arrays
          unset($entries[$sku], $topLevelEntries[$sku]);
        }
      }
    }

    // Assign prices
    foreach ($priceData->record as $priceRecord) {
      $pricePartNumber = (string) $priceRecord->partnumber;
      if (!isset($entries[$pricePartNumber])) {
        continue;
      }

      $delete = false;

      // First, if price=0 or published=0 on price record, delete the entry
      if (
          ('1' != (string) $priceRecord->published) ||
          (0.0 == (float) $priceRecord->listprice)
      ) {
        $delete = true;
      }

      if ($delete) {
        // Will always be a child entry
        unset($entries[$pricePartNumber]);
      } else {
        $price = ServiceLocator::price($priceRecord);
        $entries[$pricePartNumber]->addPrice($price);
      }
    }

    $this->assignDescriptiveAttributes($entries, $brand);

    // Do SalesStatus=30 filter here, as an optimisation
    foreach ($topLevelEntries as $sku => $entry) {
      $endecaPropsGroup = $entry->getDescriptiveAttributeGroup('EndecaProps');
      if (!is_null($endecaPropsGroup)) {
        $salesStatus30 = $endecaPropsGroup->getDescriptiveAttributeWhere([
          'description' => 'SalesStatus',
          'value' => '30'
        ]);
        if ($salesStatus30) {
          continue;
        }
      }

      // If no EndecaProps group or no SalesStatus attr, or SalesStatus != 30,
      // exclude product.
      unset($topLevelEntries[$sku], $entries[$sku]);
    }

    // Assign parent entry to all child entries via parentpartnumber field.
    // (This has to be a separate loop from above, because all entries need to
    // be indexed first.)
    foreach ($entries as $entry) {
      if ('' !== $entry->parentpartnumber) {
        // Use $topLevelEntries to look up parents as an optimisation -- it's shorter.
        if (isset($topLevelEntries[$entry->parentpartnumber], $entries[$entry->partnumber])) {
          $entries[$entry->partnumber]->setParentEntry($topLevelEntries[$entry->parentpartnumber]);
          $topLevelEntries[$entry->parentpartnumber]->addChildEntry($entries[$entry->partnumber]);
        }
      }
    }

    // Another loop through parent entries to delete those without any children
    // assigned - this will be those where price=0 or published=0 for all
    // variants.
    // TODO possible to optimize by reducing number of times looping through entries?
    foreach ($topLevelEntries as $sku => $entry) {
      $numChildEntries = count($entry->getChildEntries());
      if (0 == $numChildEntries) {
        unset($topLevelEntries[$sku], $entries[$sku]);
      }
    }

    $this->assignEntryDescriptions($entries, $brand);
    $this->assignDefiningAttributeValues($entries, $brand);

    return $topLevelEntries;
  }

  /**
   * @param FeedEntity\CatalogEntry[] $entries
   * @param string $brand
   * @return void
   */
  private function assignEntryDescriptions(array &$entries, $brand) {
    // Assign entry descriptions
    // Note: entry descriptions exist for both top-level and child part numbers,
    // but may be redundant.
    $entryDescriptionData = $this->xmlReader->readFile($brand, 'CatalogEntryDescription');
    foreach ($entryDescriptionData->record as $entryDescriptionRecord) {
      $descriptionPartNumber = (string) $entryDescriptionRecord->partnumber;
      if (isset($entries[$descriptionPartNumber])) {
        // Check if already created and added, and do so if not
        $description = $entries[$descriptionPartNumber]->getDescription();
        if (is_null($description)) {
          $description = ServiceLocator::catalogEntryDescription();
          $entries[$descriptionPartNumber]->setDescription($description);
        }
        // Now we have a reference to the description for the given catalog
        // entry, whether it already existed or was just
        // created. It's a compound record obj. Add the record for the locale
        // value we have in the current loop iteration.
        $description->initRecord($entryDescriptionRecord, (string) $entryDescriptionRecord->locale);
      }
    }
  }

  /**
   * @param FeedEntity\CatalogEntry[] $entries
   * @param string $brand
   * @return void
   */
  private function assignDefiningAttributeValues(array &$entries, $brand) {
    // Assign defining attribute values (no use attaching defining attributes,
    // the data in the definingattributevalue file are enough).
    // Note, these only exist for child entries (colour variants).
    $definingAttributeValueData = $this->xmlReader->readFile($brand, 'DefiningAttributeValue');
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
   * @param string $brand
   * @return void
   */
  private function assignDescriptiveAttributes(array &$entries, $brand) {
    // Assign descriptive attributes.
    // Note, these only exist for top-level entries, so we optimize by using
    // $topLevelEntries for lookup, since it contains references to the same
    // objects, but is shorter.
    $descriptiveAttributeData = $this->xmlReader->readFile($brand, 'DescriptiveAttribute');
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
  private function getCatalogGroups($brand) {
    $groupData = $this->xmlReader->readFile($brand, 'B2C_CatalogGroup');
    $groupRelnData = $this->xmlReader->readFile($brand, 'B2C_CatalogGroupRelationship');

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
