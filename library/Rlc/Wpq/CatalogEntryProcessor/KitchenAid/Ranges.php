<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Ranges extends Wpq\CatalogEntryProcessor\StandardAbstract {

//  public function process(Wpq\FeedEntity\CatalogEntry $entry, array $entries,
//      $locale, array &$outputData) {
//
//    $description = $entry->getDescription();
//    $entryData['gas'] = (bool) stripos($description->name, 'gas');
//    $entryData['electric'] = (bool) stripos($description->name, 'electric');
//    $entryData['induction'] = (bool) stripos($description->name, 'induction');
//    
//    if (!$entryData['electric']) {
//      return;
//    }
//
//
//    return parent::process($entry, $entries, $locale, $outputData);
//  }

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
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */

    $entryData['double'] = stripos($description->name, 'double') !== false;

    /*
     * Sales-feature-based info
     */

    // Init all to false


    if ($salesFeatureGroup) {
      
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
    return 'Ranges';
  }

}
