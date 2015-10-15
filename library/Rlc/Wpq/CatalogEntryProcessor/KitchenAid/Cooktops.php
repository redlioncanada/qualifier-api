<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Cooktops extends Wpq\CatalogEntryProcessor\StandardAbstract {

  public function process(Wpq\FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData) {

    $description = $entry->getDescription();
    $entryData['gas'] = (bool) stripos($description->name, 'gas');
    $entryData['electric'] = (bool) stripos($description->name, 'electric');
    $entryData['induction'] = (bool) stripos($description->name, 'induction');
    
    if ($entryData['induction']) {
      return;
    }


    return parent::process($entry, $entries, $locale, $outputData);
  }

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    
    
    
    



    // DEV CODE
    foreach ($entry->getDescriptiveAttributeGroups() as $grpName => $grp) {
      if (in_array($grpName, ['Endeca', 'EndecaProps'])) {
        continue;
      }
      foreach ($grp->getDescriptiveAttributes() as $attr) {
        $entryData['descr-attrs'][$grpName][] = [
          'description' => $attr->description,
          'valueidentifier' => $attr->valueidentifier,
          'value' => $attr->value,
          'noteinfo' => $attr->noteinfo,
        ];
      }
    }






    
    
    $description = $entry->getDescription();
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */

    $entryData['gas'] = (bool) stripos($description->name, 'gas');
    $entryData['electric'] = (bool) stripos($description->name, 'electric');
    $entryData['induction'] = (bool) stripos($description->name, 'induction');

    /*
     * Sales-feature-based info
     */

    // Init all to false


    if ($salesFeatureGroup) {
      
    }

    /*
     * Compare-feature-based info
     */

    $entryData['5Elements'] = false;
    $entryData['5Burners'] = false;
    $entryData['6Burners'] = false;

    if ($compareFeatureGroup) {
      $numElementsFeature = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Number of Elements-Burners");
      // TODO burners and elements are the same thing; RLC may want to change
      // this once they hear that.
      $entryData['5Elements'] = 5 <= $numElementsFeature->value;
      $entryData['5Burners'] = $entryData['5Elements'];
      $entryData['6Burners'] = 6 <= $numElementsFeature->value;
    }

    /*
     * Other
     */

//    $allCatalogGroups = $entry->getAllCatalogGroups();
//    $allCatalogGroupIds = array_map(function ($grp) {
//      return (string) $grp->identifier;
//    }, $allCatalogGroups);
//     in_array('...', $allCatalogGroupIds);
//     
    // Add image
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'kitchenaid';
  }

  protected function getCategory() {
    return 'Cooktops';
  }

}
