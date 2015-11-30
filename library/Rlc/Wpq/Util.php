<?php

namespace Rlc\Wpq;

use Lrr\ServiceLocator;

class Util {

  /**
   * Keyed by brand, then locale (3 dimensional)
   * 
   * @var array
   */
  private $productUrlsCache = [];

  /**
   * Like native trim(), but also takes care of non-breaking spaces
   * (\xA0) which sometimes appear in XML values.
   * 
   * @param string $s
   * @return string
   */
  public function trim($s) {
    $nbsp = json_decode('"\u00a0"');
    // Replace with normal space
    $s = str_replace($nbsp, ' ', $s);
    return trim($s);
  }

  /**
   * @param array $source Must be a sequential array
   * @return mixed
   */
  public function getRandomElement(array $source) {
    return $source[rand(0, count($source) - 1)];
  }

  public function getFeatureKeyForSalesFeature(FeedEntity\DescriptiveAttribute $localizedSalesFeature,
      $brand, $category) {
    $result = null;
    $salesFeatureAssocs = ServiceLocator::salesFeatureAssocs();
    if (isset($salesFeatureAssocs[$brand]) &&
        isset($salesFeatureAssocs[$brand][$category])) {
      foreach ($salesFeatureAssocs[$brand][$category] as $valueidentifier => $featureKey) {
        if ('/' === $valueidentifier[0]) {
          // Regex
          if (preg_match($valueidentifier, $localizedSalesFeature->valueidentifier)) {
            $result = $featureKey;
            break;
          }
        } else {
          // Compare case-insensitively
          if (strtolower($valueidentifier) == strtolower($localizedSalesFeature->valueidentifier)) {
            $result = $featureKey;
            break;
          }
        }
      }
    }
    return $result;
  }

  /**
   * If dimension is expressed as fraction, convert to decimal
   * 
   * @param string $dim
   * @return double
   */
  public function formatPhysicalDimension($dim) {
    $matches = [];
    if (preg_match('@(\d+)\s+(\d+)/(\d+)@', $dim, $matches)) {
      $wholeNumber = $matches[1];
      $numerator = $matches[2];
      $denominator = $matches[3];
      $decimal = $numerator / $denominator;
      $result = $wholeNumber + $decimal;
    } else {
      // Not in fraction format
      $result = $dim;
    }
    return $result;
  }

  /**
   * Gets associative array of parentpartnumber => URL
   * 
   * @param string $brand
   * @param string $locale
   * @return array or null if file not found
   */
  public function getProductUrls($brand, $locale) {
    // For capitalization in filename
    static $brandNameMap = [
      'maytag' => 'Maytag',
      'kitchenaid' => 'KitchenAid',
      'whirlpool' => 'Whirlpool',
    ];

    if (!isset($this->productUrlsCache[$brand][$locale])) {
      $this->productUrlsCache[$brand][$locale] = [];
      $filePath = realpath(
          APPLICATION_PATH
          . '/../data/source-xml/'
          . $brandNameMap[$brand]
          . '_product_feed_'
          . $locale
          . '.txt'
      );
      if (!$filePath) {
        return;
      }
      $fileHandle = fopen($filePath, 'r');
      fgetcsv($fileHandle, 0, "\t"); // Skip headers
      while ($row = fgetcsv($fileHandle, 0, "\t")) {
        // NB the file actually has >1 URL per parent sku, cause they're actually
        // for child skus, but also include parent sku. But because of the way this
        // assignment works, we'll still end up with just one URL (doesn't matter
        // which) per parent sku, which is what we want.
        $this->productUrlsCache[$brand][$locale][$row[11]] = $row[2];
      }
    }
    return $this->productUrlsCache[$brand][$locale];
  }

  public function buildChildEntryData(FeedEntity\CatalogEntry $childEntry,
      $locale) {
    $variantSku = (string) $childEntry->partnumber;

    $colourDa = $childEntry->getDefiningAttributeValue('Color');
    $newColoursElem = [
      'sku' => $variantSku,
      'colourCode' => (string) $colourDa->valueidentifier,
    ];
    $newColoursElem['colourName'] = (string) $colourDa->getRecord($locale)->value;

    /*
     * Attach price info
     */
    $prices = $childEntry->getPrices();
    foreach ($prices as $price) {
      $newColoursElem['prices'][$price->currency] = $price->listprice;
    }

    return $newColoursElem;
  }

  /**
   * Shortcut for extracting a pattern match if found
   * 
   * @param string $pattern
   * @param string $subject
   * @param int $matchIndex
   * 
   * @return string or NULL if no match
   */
  public function getPregMatch($pattern, $subject, $matchIndex = 0) {
    $matches = [];
    preg_match($pattern, $subject, $matches);
    if (count($matches)) {
      return $matches[$matchIndex];
    }
    return null;
  }

}
