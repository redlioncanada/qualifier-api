<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

/**
 * This class exists just so the dryers nested under each washer entry
 * get the basic sku/name/description/colours/salesFeatures/compareFeatures
 * fields.
 */
class Dryers extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    // Add image for dryers - no other features
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;
  }

  protected function postProcess(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale, array &$newOutputData) {
    // Don't need this field when already nested under 'dryers' array under
    // the washer
    unset($newOutputData['appliance']);
  }

  protected function getBrand() {
    return 'maytag';
  }

  protected function getCategory() {
    return 'Dryers';
  }

}
