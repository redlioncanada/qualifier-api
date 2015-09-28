<?php

namespace Rlc\Wpq\CatalogEntryProcessor\KitchenAid;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Vents extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'kitchenaid';
  }

  protected function getCategory() {
    
  }

}
