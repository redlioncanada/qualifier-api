<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Maytag;

use Rlc\Wpq,
    Lrr\Translator,
    Lrr\ServiceLocator,
    Rlc\Wpq\FeedEntity;

class LaundryPairs implements Wpq\CatalogEntryProcessorInterface {

  /**
   * @var Util
   */
  private $util;

  /**
   * @var Translator
   */
  private $translator;

  /**
   * List of sorted sku pairs to keep track of which laundry pairs we've
   * already processed, indexed first by LOCALE. (There is one instance of this
   * class per HTTP request.)
   * 
   * @var array
   */
  private $laundryPairsProcessed = [];

  public function __construct() {
    $this->util = ServiceLocator::util();
    $this->translator = ServiceLocator::translator();
  }

  public function process(Wpq\FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData) {

    // Init array of pairs to process right now
    $laundryPairsToProcessThisRound = [];
    // Ensure initialization of array of all pairs processed for this locale
    if (!isset($this->laundryPairsProcessed[$locale])) {
      $this->laundryPairsProcessed[$locale] = [];
    }

    $assocParentSkus = $this->getAssocParentSkus($entry, $entries);
    $productUrls = $this->util->getProductUrls('maytag', $locale);

    /**
     * Collect all laundry pairs involving this washer or dryer
     */
    foreach ($assocParentSkus as $assocParentSku) {
      // Create a key for the pair to make the combination unique, even
      // with the association in reverse (via sort())
      $vPairKey = [$entry->partnumber, $assocParentSku];
      sort($vPairKey);
      $pairKey = implode('|', $vPairKey);
      // Check if we've already processed it. (E.g. if process() was called for
      // the dryer of the pair, but we've already processed the same pair when
      // process() was called for the washer. process() is called for every
      // product, so this is normal.)
      if (!in_array($pairKey, $this->laundryPairsProcessed[$locale])) {
        // Don't process this pair again
        $this->laundryPairsProcessed[$locale][] = $pairKey;

        if ($entry->isInGroupId('SC_Laundry_Laundry_Appliances_Washers')) {
          // Current product is washer
          $washerSku = $entry->partnumber;
          $dryerSku = $assocParentSku;
        } else {
          // Current product is dryer
          $washerSku = $assocParentSku;
          $dryerSku = $entry->partnumber;
        }
        $laundryPairsToProcessThisRound[$pairKey] = [
          'data' => ['appliance' => 'Laundry'],
          'washerSku' => $washerSku,
          'dryerSku' => $dryerSku,
        ];
      }
    }

    /*
     * Now that all laundry pairs are collected, process and add them to the output data
     */
    foreach ($laundryPairsToProcessThisRound as $laundryPair) {
      $newOutputData = $this->buildLaundryPairData($laundryPair, $entries, $locale);
      // Link to washer page
      $newOutputData['url'] = $productUrls[$newOutputData['washerSku']];
      $outputData[] = $newOutputData;
    }
  }

  private function buildLaundryPairData(array $laundryPair, array $entries,
      $locale) {
    $brand = 'maytag'; // kept as a var in case i want to reuse this block for whirlpool
    $data = $laundryPair['data'];
    $washer = $entries[$laundryPair['washerSku']];
    $dryer = $entries[$laundryPair['dryerSku']];

    // Get all the pieces we'll be analysing
    $washerDescription = $washer->getDescription()->getRecord($locale);
    $washerDescriptionDefaultLocale = $washer->getDescription()->getRecord();
    $washerCompareFeatureGroup = $washer->getDescriptiveAttributeGroup('CompareFeature');
    $washerSalesFeatureGroup = $washer->getDescriptiveAttributeGroup('SalesFeature');
    $washerMiscGroup = $washer->getDescriptiveAttributeGroup('Miscellaneous');
    $dryerDescription = $dryer->getDescription()->getRecord($locale);
    $dryerDescriptionDefaultLocale = $dryer->getDescription()->getRecord();
    $dryerCompareFeatureGroup = $dryer->getDescriptiveAttributeGroup('CompareFeature');
    $dryerSalesFeatureGroup = $dryer->getDescriptiveAttributeGroup('SalesFeature');
    $dryerMiscGroup = $dryer->getDescriptiveAttributeGroup('Miscellaneous');

    // Sku/Name/description
    $data['washerSku'] = $laundryPair['washerSku'];
    $data['dryerSku'] = $laundryPair['dryerSku'];
    $data['name'] = $washerDescription->name . ' ' . $this->translator->translate('and_dryer', $locale);
    $data['washerName'] = (string) $washerDescription->name;
    $data['dryerName'] = (string) $dryerDescription->name;
    $data['washerDescription'] = (string) $washerDescription->longdescription;
    $data['dryerDescription'] = (string) $dryerDescription->longdescription;

    /*
     * Combine washer/dryer colours for pairs - all members of pairs seem to
     * come in the same corresponding colours.
     */
    $washerChildEntries = $washer->getChildEntries();
    $dryerChildEntries = $dryer->getChildEntries();
    $washerColours = $dryerColoursByCode = [];
    foreach ($washerChildEntries as $childEntry) {
      $childEntryData = $this->util->buildChildEntryData($childEntry, $locale);
      $washerColours[] = $childEntryData;
    }
    // Index dryer colours by code so they can be plucked out in the next loop
    foreach ($dryerChildEntries as $childEntry) {
      $childEntryData = $this->util->buildChildEntryData($childEntry, $locale);
      $dryerColoursByCode[$childEntryData['colourCode']] = $childEntryData;
    }
    // Combine together, assuming codes will match
    $data['colours'] = [];
    foreach ($washerColours as $washerColour) {
      if (!isset($dryerColoursByCode[$washerColour['colourCode']])) {
        // Only include colours that the washer comes in and the dryer also comes in.
        continue;
      }
      $dryerColour = $dryerColoursByCode[$washerColour['colourCode']];
      $newColour = [
        'name' => $washerColour['colourName'],
        'code' => $washerColour['colourCode'],
        'washerSku' => $washerColour['sku'],
        'dryerSku' => $dryerColour['sku'],
        'washerPrices' => $washerColour['prices'],
        'dryerPrices' => $dryerColour['prices'],
      ];
      $data['colours'][] = $newColour;
    }

    /*
     * Laundry features
     */
    $data['washerCapacity'] = (float) preg_replace('@^.*(\d+(?:\.\d+))\s+cu\. ft\..*$@is', '$1', $washerDescriptionDefaultLocale->name);
    $data['dryerCapacity'] = (float) preg_replace('@^.*(\d+(?:\.\d+))\s+cu\. ft\..*$@is', '$1', $dryerDescriptionDefaultLocale->name);

    // Init some values to ensure they exist
    $data['vibrationControl'] = false;
    $data['rapidWash'] = false;
    $data['stacked'] = false;
    $data['sensorDry'] = false;
    $data['rapidDry'] = false;
    $data['cycleOptions'] = 0;
    $data['washerCycleOptions'] = 0;
    $data['dryerCycleOptions'] = 0;

    /*
     * Washer features
     */

    if ($washerCompareFeatureGroup) {
      $avcAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => 'Advanced Vibration Control']);
      if ($avcAttr) {
        $data['vibrationControl'] = !in_array($avcAttr->value, ["No", "None"]);
      }

      // Store # of cycle options for washer, and increment total cycle options number
      $washerCyclesAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Wash Cycles"]);
      if ($washerCyclesAttr) {
        $data['cycleOptions'] += $washerCyclesAttr->value;
        $data['washerCycleOptions'] = (int) $washerCyclesAttr->value;
      }
    }

    $data['frontLoad'] = (
        (false !== stripos($washerDescriptionDefaultLocale->name, 'front load')) ||
        (false !== stripos($washerDescriptionDefaultLocale->longdescription, 'front load'))
        );
    $data['topLoad'] = !$data['frontLoad'];

    if ($washerSalesFeatureGroup) {
      // Just has to exist
      $data['rapidWash'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Rapid Wash Cycle"]);
      $data['washerWrinkleControl'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Wrinkle Control Cycle"]);
      $data['steamEnhanced'] = (bool) $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Steam-Enhanced Cycles"]);
      // TODO or this too? $washerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Steam-Enhanced Dryer"])
      // Depends on if this has to do with dryer -- waiting for answer from RLC
    }

    /*
     * Dryer features
     */

    $data['soundGuard'] = (
        (false !== stripos($dryerDescription->name, 'soundguard')) ||
        (false !== stripos($dryerDescription->longdescription, 'soundguard'))
        );

    if ($dryerCompareFeatureGroup) {
      // Stacked is actually a feature of both, but the value we look for is under
      // the dryer attrs.
      $stackableAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Stackable"]);
      if ($stackableAttr) {
        $data['stacked'] = ("Yes" == $stackableAttr->value);
      }

      // Sensor dry
      $moistureSensorAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Moisture Sensor"]);
      if ($moistureSensorAttr) {
        $data['sensorDry'] = ("Yes" == $moistureSensorAttr->value);
      }

      // Store # of cycle options for dryer, and increment total cycle options number
      $dryerCyclesAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Cycles"]);
      if ($dryerCyclesAttr) {
        $data['cycleOptions'] += $dryerCyclesAttr->value;
        $data['dryerCycleOptions'] = (int) $dryerCyclesAttr->value;
      }
    }

    if ($dryerSalesFeatureGroup) {
      // Just has to exist
      $data['rapidDry'] = (bool) $dryerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Rapid Dry Cycle"]);
      $data['dryerWrinkleControl'] = (bool) $dryerSalesFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => "Wrinkle Control Cycle"]);
    }

    /*
     * Pair image
     */
    // Goes before image URLs in feed to make them relative to http://maytag.com
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;
    $galleryGroup = $washer->getDescriptiveAttributeGroup('Gallery');
    if ($galleryGroup) {
      foreach ($galleryGroup->getDescriptiveAttributes(null, $locale) as $attr) {
        // Check if we've found the right attr
        if (false === strpos($attr->image1, 'Pair_244X312_')) {
          continue;
        }
        // Split up urls and find the one of the right dimensions
        $imageUrls = explode('|', $attr->image1);
        foreach ($imageUrls as $imageUrl) {
          if (false !== strpos($imageUrl, 'Pair_244X312_')) {
            $data['image'] = $imageUrlPrefix . $imageUrl;
            break 2;
          }
        }
      }
    }
    // If still not set, use no image image
    if (!isset($data['image'])) {
      $data['image'] = $imageUrlPrefix . '/No Image Available/Standalone_244X312.png';
    }

    /*
     * Attach sales feature data
     */
    $data['salesFeatures'] = [];

    // Washers
    foreach ($washerSalesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $new = [
        // Check if it's a qualified feature and put in the association
        'featureKey' => $this->util->getFeatureKeyForSalesFeature($localizedSalesFeature, $brand, 'Laundry-Washers'),
        'top3' => ($localizedSalesFeature->valuesequence <= 3), // double check using field for this purpose - is it same as sequence?
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $data['salesFeatures'][] = $new;
    }

    // Dryers
    foreach ($dryerSalesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $new = [
        'featureKey' => $this->util->getFeatureKeyForSalesFeature($localizedSalesFeature, $brand, 'Laundry-Dryers'),
        'top3' => ($localizedSalesFeature->valuesequence <= 3),
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $data['salesFeatures'][] = $new;
    }
    
    /*
     * Attach compare feature data (for print view)
     */
    $data['washerCompareFeatures'] = [];
    $data['dryerCompareFeatures'] = [];

    // Washers
    if ($washerCompareFeatureGroup) {
      foreach ($washerCompareFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedCompareFeature) {
        $data['washerCompareFeatures'][$localizedCompareFeature->description][$localizedCompareFeature->valueidentifier] = $localizedCompareFeature->value;
      }
    }

    // Dryers
    if ($dryerCompareFeatureGroup) {
      foreach ($dryerCompareFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedCompareFeature) {
        $data['dryerCompareFeatures'][$localizedCompareFeature->description][$localizedCompareFeature->valueidentifier] = $localizedCompareFeature->value;
      }
    }
    
    /*
     * Add disclaimer data
     */
    
    // Washers
    $washerDisclaimersTemp = [];
    foreach ($washerMiscGroup->getDescriptiveAttributes(['description' => "Disclaimer"], $locale) as $localizedDisclaimer) {
      $washerDisclaimersTemp[$localizedDisclaimer->sequence] = $localizedDisclaimer->value;
    }
    ksort($washerDisclaimersTemp, SORT_NUMERIC);
    // Convert to sequential array after sorting
    $data['washerDisclaimers'] = array_values($washerDisclaimersTemp);
    
    // Dryers
    $dryerDisclaimersTemp = [];
    foreach ($dryerMiscGroup->getDescriptiveAttributes(['description' => "Disclaimer"], $locale) as $localizedDisclaimer) {
      $dryerDisclaimersTemp[$localizedDisclaimer->sequence] = $localizedDisclaimer->value;
    }
    ksort($dryerDisclaimersTemp, SORT_NUMERIC);
    // Convert to sequential array after sorting
    $data['dryerDisclaimers'] = array_values($dryerDisclaimersTemp);

    return $data;
  }

  private function getAssocParentSkus(FeedEntity\CatalogEntry $entry,
      array $allEntries) {
    $assocParentSkus = [];
    $assocChildSkus = [];
    $childSkusOfThisEntry = [];

    foreach ($entry->getChildEntries() as $childEntry) {
      $childSkusOfThisEntry[] = $childEntry->partnumber;
      $endecaPropsGroup = $childEntry->getDescriptiveAttributeGroup('EndecaProps');
      if ($endecaPropsGroup) {
        $pairIdAttrs = $endecaPropsGroup->getDescriptiveAttributes(['description' => 'PairId']);
        foreach ($pairIdAttrs as $pairIdAttr) {
          $assocChildSkus = array_merge($assocChildSkus, explode('|', $pairIdAttr->value));
        }
      }
    }

    $assocChildSkus = array_unique(array_diff($assocChildSkus, $childSkusOfThisEntry));
    foreach ($assocChildSkus as $assocChildSku) {
      if (isset($allEntries[$assocChildSku])) {
        $parentPartNumber = $allEntries[$assocChildSku]->parentpartnumber;
        if (isset($allEntries[$parentPartNumber])) {
          $assocParentSkus[] = $parentPartNumber;
        }
      }
    }

    // Make unique and get rid of empties
    $assocParentSkus = array_unique(array_filter($assocParentSkus));

    return $assocParentSkus;
  }

}
