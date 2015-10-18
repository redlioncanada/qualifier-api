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
   * @return array of DescriptiveAttribute[] or empty [] if no matching records
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
   * @param string  $valueidentifier
   * @param string  $locale   OPTIONAL
   * @return DescriptiveAttribute or NULL if no matching records
   */
  public function getDescriptiveAttributeByValueIdentifier($valueidentifier,
      $locale = null) {
    $attr = $this->getDescriptiveAttributeWhere(["valueidentifier" => $valueidentifier], $locale);
    return $attr;
  }

  /**
   * @param string  $pattern
   * @param int     $limit    OPTIONAL default no limit (0)
   * @param bool    $regex    OPTIONAL default false
   * @param string  $locale   OPTIONAL
   * @return array of DescriptiveAttribute[] (empty array if no matches)
   */
  public function getDescriptiveAttributesByValueIdentifierMatch($pattern,
      $limit = 0, $regex = false, $locale = null) {

    if (is_null($locale)) {
      $locale = $this->defaultLocale;
    }

    $results = [];
    foreach ($this->attrsByLocale[$locale] as $attr) {
      if (
          ($regex && !preg_match($pattern, $attr->valueidentifier)) ||
          (!$regex && (false === stripos($attr->valueidentifier, $pattern)))
      ) {
        // No match
        continue;
      }
      // Match
      $results[] = $attr;
      if ($limit && count($results) >= $limit) {
        break;
      }
    }

    return $results;
  }

  /**
   * @param array   $criteria Associative field => value conditions
   * @param string  $locale   OPTIONAL
   * @return bool
   */
  public function descriptiveAttributeExistsWhere(array $criteria,
      $locale = null) {
    $attr = $this->getDescriptiveAttributeWhere($criteria, $locale);
    return (bool) $attr;
  }

  /**
   * @param string  $valueidentifier
   * @param string  $locale   OPTIONAL
   * @return bool
   */
  public function descriptiveAttributeExistsByValueIdentifier($valueidentifier,
      $locale = null) {
    $attr = $this->getDescriptiveAttributeWhere(["valueidentifier" => $valueidentifier], $locale);
    return (bool) $attr;
  }

  /**
   * @param string  $pattern
   * @param bool    $regex    OPTIONAL default false
   * @param string  $locale   OPTIONAL
   * @return bool
   */
  public function descriptiveAttributeExistsByValueIdentifierMatch($pattern,
      $regex = false, $locale = null) {
    $results = $this->getDescriptiveAttributesByValueIdentifierMatch($pattern, 1, $regex, $locale);
    return !empty($results);
  }

}
