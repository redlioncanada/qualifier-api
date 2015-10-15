<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Vents extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    
    
    
    
    
//
//
//    // DEV CODE
//    foreach ($entry->getDescriptiveAttributeGroups() as $grpName => $grp) {
//      if (in_array($grpName, ['Endeca', 'EndecaProps'])) {
//        continue;
//      }
//      foreach ($grp->getDescriptiveAttributes() as $attr) {
//        $entryData['descr-attrs'][$grpName][] = [
//          'description' => $attr->description,
//          'valueidentifier' => $attr->valueidentifier,
//          'value' => $attr->value,
//          'noteinfo' => $attr->noteinfo,
//        ];
//      }
//    }
//
//
//
//    
//    
//    
    $description = $entry->getDescription();
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */



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
    
  }

}
