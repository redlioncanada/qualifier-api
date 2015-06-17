<?php

namespace Rlc\Wpq\FeedEntity;

use Lrr\ServiceLocator;

class DescriptiveAttributeGroup {

  /**
   * 2D array, first keyed by locale, then just sequential.
   * E.g. to access the 5th record for en_CA: $this->records['en_CA'][4]
   * 
   * @var DescriptiveAttribute[][]
   */
  private $recordsByLocale = [];

  public function loadRecord(\SimpleXMLElement $record) {
    $locale = (string) $record->locale;
    if (!isset($this->recordsByLocale[$locale])) {
      $this->recordsByLocale[$locale] = [];
    }
    $this->recordsByLocale[$locale][] = ServiceLocator::descriptiveAttribute($record);
  }

  // TODO will prob need some methods for searching values
}
