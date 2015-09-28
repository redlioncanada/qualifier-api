<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Maytag;

use Rlc\Wpq,
    Lrr\ServiceLocator;

/**
 * Parent class of Wall Oven and Range.
 */
abstract class CookingAppliances extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $entryData['type'] = $this->getType();

    $description = $entry->getDescription(); // property retrieval will use default locale
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');

    // Goes before image URLs in feed to make them relative to http://maytag.com
    $imageUrlPrefix = '/digitalassets';

    /*
     * These are all the wall oven features, and they also apply to ranges.
     */

    // Default bools to false
    $entryData['powerPreheat'] = false;

    $entryData['combination'] = stripos($description->name, 'combination') !== false;
    $entryData['double'] = stripos($description->name, 'double') !== false;
    $entryData['single'] = !$entryData['double'];
    $entryData['trueConvection'] = (
        stripos($description->name, 'evenair') !== false ||
        stripos($description->longdescription, 'evenair') !== false ||
        stripos($description->name, 'true convection') !== false ||
        stripos($description->longdescription, 'true convection') !== false
        );

    if ($salesFeatureGroup) {
      if (stripos($description->longdescription, "power preheat") !== false) {
        $entryData['powerPreheat'] = true;
      } else {
        $powerPreheatAttr = $salesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Power Preheat"]);
        $entryData['powerPreheat'] = (bool) $powerPreheatAttr;
      }
    }

    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'maytag';
  }

  protected function getCategory() {
    return 'Cooking';
  }

  /**
   * @return string 'Ovens' or 'Ranges'
   */
  abstract protected function getType();
}
