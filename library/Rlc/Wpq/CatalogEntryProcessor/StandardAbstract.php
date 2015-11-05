<?php

namespace Rlc\Wpq\CatalogEntryProcessor;

use Rlc\Wpq,
    Lrr\ServiceLocator;

abstract class StandardAbstract implements Wpq\CatalogEntryProcessorInterface {

  /**
   * @var Wpq\Util
   */
  protected $util;

  public function __construct() {
    $this->util = ServiceLocator::util();
  }

  public function process(Wpq\FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData) {

    if (!$this->filterEntries($entry, $entries, $locale)) {
      return;
    }
    
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $miscGroup = $entry->getDescriptiveAttributeGroup('Miscellaneous');

    $newOutputData = [];
    $newOutputData['appliance'] = $this->getCategory();
    $newOutputData['sku'] = $entry->partnumber;

    $this->attachCatalogEntryDescriptionData($newOutputData, $entry, $locale);

    $childEntries = $entry->getChildEntries();
    $colourRecords = [];
    foreach ($childEntries as $childEntry) {
      $childEntryData = $this->util->buildChildEntryData($childEntry, $locale);
      if (isset($colourRecords[$childEntryData['colourCode']])) {
        // If any colour is represented >once, prefer the colours with SKUs
        // NOT ending in "DB" or "DW".
        // https://trello.com/c/rT5KGVO0/105-duplicate-colour-swatches-filter-for-removing-duplicate-colours
        // TODO Make conditional on brand? Other brands currently have no
        // duplicates so this would currently have no effect on them.
        $skuColourSuffix = substr($childEntryData['sku'], -2);
        if (in_array($skuColourSuffix, ['DB', 'DW'])) {
          continue;
        }
      }
      $colourRecords[$childEntryData['colourCode']] = $childEntryData;
    }
    // Make $colourRecords back into a sequential array
    $newOutputData['colours'] = array_values($colourRecords);

    $this->attachFeatureData($newOutputData, $entry, $locale);

    $productUrls = $this->util->getProductUrls($this->getBrand(), $locale);
    $newOutputData['url'] = isset($productUrls[$entry->partnumber]) ? $productUrls[$entry->partnumber] : null;

    /*
     * Attach sales feature data
     */
    $newOutputData['salesFeatures'] = [];
    foreach ($salesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $newSalesFeatureData = [
        // Check if it's a qualified feature and put in the association
        'featureKey' => $this->util->getFeatureKeyForSalesFeature($localizedSalesFeature, $this->getBrand(), $this->getCategory()),
        'top3' => ($localizedSalesFeature->valuesequence <= 3), // double check using field for this purpose - is it same as sequence?
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $newOutputData['salesFeatures'][] = $newSalesFeatureData;
    }

    /*
     * Attach compare feature data (for print view)
     */
    $newOutputData['compareFeatures'] = [];
    if ($compareFeatureGroup) {
      foreach ($compareFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedCompareFeature) {
        $newOutputData['compareFeatures'][$localizedCompareFeature->description][$localizedCompareFeature->valueidentifier] = $localizedCompareFeature->value;
      }
    }

    /*
     * Add disclaimer data
     */
    $disclaimersTemp = [];
    foreach ($miscGroup->getDescriptiveAttributes(['description' => "Disclaimer"], $locale) as $localizedDisclaimer) {
      $disclaimersTemp[$localizedDisclaimer->sequence] = $localizedDisclaimer->value;
    }
    ksort($disclaimersTemp, SORT_NUMERIC);
    // Convert to sequential array after sorting
    $newOutputData['disclaimers'] = array_values($disclaimersTemp);

    // Give a chance for subclass to add to final processing
    $this->postProcess($entry, $entries, $locale, $newOutputData);

    /**
     * Finally add to final output data
     */
    $outputData[] = $newOutputData;
  }

  /**
   * @return void
   */
  protected function attachCatalogEntryDescriptionData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $localeRecord = $description->getRecord($locale);
    $entryData['name'] = (string) $localeRecord->name;
    $entryData['description'] = (string) $localeRecord->longdescription;
  }

  /**
   * @return void
   */
  protected function attachPhysicalDimensionData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry) {
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');

    if ($compareFeatureGroup) {
      /*
       * The same method of extracting physical dimensions is shared for most categories
       */

      // Width
      $widthAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Width",
      ]);
      if ($widthAttr) {
        $entryData['width'] = $this->util->formatPhysicalDimension($widthAttr->value);
      }

      // Height
      $heightAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Height",
      ]);
      if ($heightAttr) {
        $entryData['height'] = $this->util->formatPhysicalDimension($heightAttr->value);
      }

      // Depth
      $depthAttr = $compareFeatureGroup->getDescriptiveAttributeWhere([
        'description' => "Dimensions",
        'valueidentifier' => "Depth",
      ]);
      if ($depthAttr) {
        $entryData['depth'] = $this->util->formatPhysicalDimension($depthAttr->value);
      }
    }
  }

  /**
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $entry
   * @param array $entries        \Rlc\Wpq\FeedEntity\CatalogEntry[]
   * @param string $locale
   * @param array $newOutputData  Difference from process() arguments -- this is
   *                              a reference to the single new record created
   *                              by process(), not the set of all records so far.
   * 
   * @return void
   */
  protected function postProcess(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale, array &$newOutputData) {
    // Nothing by default
  }


  /**
   * Decide whether to include a given entry in the results. Defaults to include
   * everything.
   * 
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $entry
   * @param array $entries        \Rlc\Wpq\FeedEntity\CatalogEntry[]
   * @param string $locale
   * 
   * @return bool true = include the entry and continue processing, or
   * false = discard
   */
  protected function filterEntries(Wpq\FeedEntity\CatalogEntry $entry,
      array $entries, $locale) {
    return true;
  }

  /**
   * @param array $entryData REFERENCE
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $entry
   * @param string $locale
   * 
   * @return void
   */
  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    // Nothing by default
  }

  /**
   * @return string
   */
  abstract protected function getBrand();

  /**
   * @return string
   */
  abstract protected function getCategory();
}
