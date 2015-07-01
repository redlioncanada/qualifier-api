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
    $this->recordsByLocale[$locale][(string) $record->description] = ServiceLocator::descriptiveAttribute($record);
  }

  /**
   * @param string  $attributeDescription
   * @param string  $locale               OPTIONAL
   * @return string or null if attribute or locale does not exist
   */
  public function getDescriptiveAttributeValueAsString($attributeDescription,
      $locale = null) {
    if (is_null($locale)) {
      $locale = $this->defaultLocale;
    }
    if (isset($this->recordsByLocale[$locale][$attributeDescription])) {
      return (string) $this->recordsByLocale[$locale][$attributeDescription]->value;
    } else {
      return null;
    }
  }

}
