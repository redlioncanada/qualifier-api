<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Cooktops extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */

    $entryData['induction'] = false !== stripos($description->name, 'induction');
    $entryData['electric'] = !$entryData['induction'] && (false !== stripos($description->name, 'electric'));
    $entryData['gas'] = false !== stripos($description->name, 'gas');
    $entryData['accuSimmer'] = false !== stripos($description->longdescription, "AccuSimmer");

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['dishwasherSafeKnobs'] = false;
    $entryData['glassTouch'] = false;

    if ($salesFeatureGroup) {
      $entryData['dishwasherSafeKnobs'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Dishwasher-Safe Knobs");
      $entryData['glassTouch'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Glass Touch Controls");

      if (!$entryData['accuSimmer']) {
        // If not found in description, try for SF
        $entryData['accuSimmer'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch("AccuSimmer");
      }
    }

    /*
     * Other
     */

    // Add image
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Cooktops';
  }

}
