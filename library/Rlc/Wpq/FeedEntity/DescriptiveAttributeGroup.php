<?php

namespace Rlc\Wpq\FeedEntity;

use Lrr\ServiceLocator;

class DescriptiveAttributeGroup {

  /**
   * 2D array, first keyed by locale, then description
   * E.g. to access the 'SalesStatus' record for en_CA: $this->records['en_CA']['SalesStatus']
   * 
   * @var DescriptiveAttribute[][]
   */
  private $recordsByLocale = [];

  /**
   * @var string
   */
  private $defaultLocale;

  public function __construct($defaultLocale) {
    $this->defaultLocale = $defaultLocale;
  }

  public function loadRecord(\SimpleXMLElement $record) {
    $locale = (string) $record->locale;
    if (!isset($this->recordsByLocale[$locale])) {
      $this->recordsByLocale[$locale] = [];
    }
    $this->recordsByLocale[$locale][] = ServiceLocator::descriptiveAttribute($record);
  }

  public function getDescriptiveAttributes($locale = null) {
    if (is_null($locale)) {
      $locale = $this->defaultLocale;
    }
    if (isset($this->recordsByLocale[$locale])) {
      return $this->recordsByLocale[$locale];
    } else {
      return [];
    }
  }

  public function getDescriptiveAttributeWhere(array $criteria, $locale = null) {
    if (is_null($locale)) {
      $locale = $this->defaultLocale;
    }
    
    foreach ($this->recordsByLocale[$locale] as $record) {
      foreach ($criteria as $field => $value) {
        if ($value != $record->$field) {
          continue 2;
        }
      }
      // All values match in record
      return $record;
    }

    // No matching records found
    return null;
  }

}
