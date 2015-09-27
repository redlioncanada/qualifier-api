<?php

namespace Rlc\Wpq\FeedEntity;

use Lrr\ServiceLocator;

class DescriptiveAttributeGroup {

  /**
   * 2D array, first keyed by locale, then sequentially
   * 
   * @var DescriptiveAttribute[][]
   */
  private $attrsByLocale = [];

  /**
   * @var string
   */
  private $defaultLocale;

  public function __construct($defaultLocale) {
    $this->defaultLocale = $defaultLocale;
  }

  public function loadRecord(\SimpleXMLElement $record) {
    $locale = (string) $record->locale;
    if (!isset($this->attrsByLocale[$locale])) {
      $this->attrsByLocale[$locale] = [];
    }
    $this->attrsByLocale[$locale][] = ServiceLocator::descriptiveAttribute($record);
  }

  /**
   * Retrieve a set of DescriptiveAttribute, either all of them or a subset
   * if $criteria is given
   * 
   * @param array   $criteria OPTIONAL Associative field => value conditions
   * @param string  $locale   OPTIONAL
   * @return DescriptiveAttribute[] or empty [] if no matching records
   */
  public function getDescriptiveAttributes(array $criteria = null,
      $locale = null) {
    if (is_null($locale)) {
      $locale = $this->defaultLocale;
    }
    if (isset($this->attrsByLocale[$locale])) {
      if (is_array($criteria) && count($criteria)) {
        $results = [];
        foreach ($this->attrsByLocale[$locale] as $attr) {
          foreach ($criteria as $field => $value) {
            if ($value != $attr->$field) {
              continue 2;
            }
          }
          // If we reach here, add the attribute to the result set
          $results[] = $attr;
        }
        return $results;
      } else {
        return $this->attrsByLocale[$locale];
      }
    } else {
      return [];
    }
  }

  /**
   * Retrieve a single DescriptiveAttribute
   * 
   * @param array   $criteria Associative field => value conditions
   * @param string  $locale   OPTIONAL
   * @return DescriptiveAttribute or NULL if no matching records
   */
  public function getDescriptiveAttributeWhere(array $criteria, $locale = null) {
    if (is_null($locale)) {
      $locale = $this->defaultLocale;
    }

    foreach ($this->attrsByLocale[$locale] as $attr) {
      foreach ($criteria as $field => $value) {
        if ($value != $attr->$field) {
          continue 2;
        }
      }
      // All values match in record
      return $attr;
    }

    // No matching records found
    return null;
  }

  /**
   * @param string $value
   * @return bool
   */
  public function descriptiveAttributeExistsByValueIdentifier($value) {
    $attr = $this->getDescriptiveAttributeWhere(["valueidentifier" => $value]);
    return (bool) $attr;
  }

}
