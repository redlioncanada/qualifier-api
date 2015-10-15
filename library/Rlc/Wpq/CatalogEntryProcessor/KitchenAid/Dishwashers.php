<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Dishwashers extends Wpq\CatalogEntryProcessor\StandardAbstract {

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

    // Decibels
    $entryData['decibels'] = null;
    $decibelMatches = [];
    if (preg_match('@(\d+) dBA@', $description->name, $decibelMatches)) {
      $entryData['decibels'] = $decibelMatches[1];
    }

    // Pocket handle console
    $entryData['pocketHandleConsole'] = (false !== strpos($description->name, "Pocket Handle"));

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['bottleWash'] = false;
    $entryData['proDry'] = false;
    $entryData['proScrub'] = false;
    $entryData['proWash'] = false;
    $entryData['cleanWater'] = false;
    $entryData['thirdLevelRack'] = false;
    $entryData['panelReady'] = false;
    $entryData['culinaryCaddy'] = false;

    if ($salesFeatureGroup) {
      // All are yes if feature exists, no otherwise
      $entryData['bottleWash'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Bottle Wash");
      $entryData['proDry'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"Advanced ProDry\u2122 System"'));
      $entryData['proScrub'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ProScrub\u00ae Option"'));
      $entryData['proWash'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"ProWash\u2122 Cycle"'));
      $entryData['cleanWater'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Clean Water Wash System");
      $entryData['thirdLevelRack'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Third Level Rack");
      $entryData['panelReady'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Panel-Ready Design");
      $entryData['culinaryCaddy'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Culinary Caddy");
    }

    /*
     * Other
     */

    // FID
    $allCatalogGroups = $entry->getAllCatalogGroups();
    $allCatalogGroupIds = array_map(function ($grp) {
      return (string) $grp->identifier;
    }, $allCatalogGroups);
    $entryData['FID'] = in_array('SC_Major_Appliances_Dishwashers_Dishwashers_Fully_Integrated', $allCatalogGroupIds);

    // Add image for dishwashers
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function getBrand() {
    return 'kitchenaid';
  }

  protected function getCategory() {
    return 'Dishwashers';
  }

}
