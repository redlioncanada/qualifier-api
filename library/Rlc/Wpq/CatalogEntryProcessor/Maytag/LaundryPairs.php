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
   *
   * @var Translator
   */
  private $translator;

  public function __construct() {
    $this->util = ServiceLocator::util();
    $this->translator = ServiceLocator::translator();
  }

  public function process(Wpq\FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData) {

    $laundryPairs = [];
    $assocParentSkus = $this->getAssocParentSkus($entry, $entries);
    $productUrls = $this->util->getProductUrls('maytag');

    /**
     * Collect all laundry pairs involving this washer or dryer
     */
    foreach ($assocParentSkus as $assocParentSku) {
      // Create a key for the pair to make the combination unique, even
      // with the association in reverse (via sort())
      $vPairKey = [$entry->partnumber, $assocParentSku];
      sort($vPairKey);
      $pairKey = implode('|', $vPairKey);
      if (!isset($laundryPairs[$pairKey])) {
        if ($entry->isInGroupId('SC_Laundry_Laundry_Appliances_Washers')) {
          // Current product is washer
          $washerSku = $entry->partnumber;
          $dryerSku = $assocParentSku;
        } else {
          // Current product is dryer
          $washerSku = $assocParentSku;
          $dryerSku = $entry->partnumber;
        }
        $laundryPairs[$pairKey] = [
          'data' => ['appliance' => $this->translator->translate('laundry', $locale)],
          'washerSku' => $washerSku,
          'dryerSku' => $dryerSku,
        ];
      }
    }

    /*
     * Now that all laundry pairs are collected, add them to the output data
     */
    foreach ($laundryPairs as $laundryPair) {
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

    $washerDescription = $washer->getDescription()->getRecord($locale);
    $washerDescriptionDefaultLocale = $washer->getDescription()->getRecord();
    $dryerDescription = $dryer->getDescription()->getRecord($locale);
    $dryerDescriptionDefaultLocale = $dryer->getDescription()->getRecord();

    // Sku/Name/description
    $data['washerSku'] = $laundryPair['washerSku'];
    $data['dryerSku'] = $laundryPair['dryerSku'];
    $data['name'] = $washerDescription->name . ' ' . ServiceLocator::translator()->translate('and_dryer', $locale);
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

    /*
     * Washer features
     */
    $washerCompareFeatureGroup = $washer->getDescriptiveAttributeGroup('CompareFeature');
    $dryerCompareFeatureGroup = $dryer->getDescriptiveAttributeGroup('CompareFeature');
    $washerSalesFeatureGroup = $washer->getDescriptiveAttributeGroup('SalesFeature');
    $dryerSalesFeatureGroup = $dryer->getDescriptiveAttributeGroup('SalesFeature');

    if ($washerCompareFeatureGroup) {
      $avcAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(['valueidentifier' => 'Advanced Vibration Control']);
      if ($avcAttr) {
        $data['vibrationControl'] = !in_array($avcAttr->value, ["No", "None"]);
      }

      $washerCyclesAttr = $washerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Wash Cycles"]);
      if ($washerCyclesAttr) {
        $data['cycleOptions'] += $washerCyclesAttr->value;
      }
      $dryerCyclesAttr = $dryerCompareFeatureGroup->getDescriptiveAttributeWhere(["valueidentifier" => "Number of Cycles"]);
      if ($dryerCyclesAttr) {
        $data['cycleOptions'] += $dryerCyclesAttr->value;
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
