<?php

namespace Rlc\Wpq\CatalogEntryProcessor;

use Rlc\Wpq,
    Lrr\ServiceLocator,
    Rlc\Wpq\Util;

abstract class StandardAbstract implements Wpq\CatalogEntryProcessorInterface {

  /**
   * @var Util
   */
  protected $util;

  public function __construct() {
    $this->util = ServiceLocator::util();
  }

  public function process(Wpq\FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData) {
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');

    $newOutputData['sku'] = $entry->partnumber;

    $this->attachCatalogEntryDescriptionData($newOutputData, $entry, $locale);

    $childEntries = $entry->getChildEntries();
    foreach ($childEntries as $childEntry) {
      $childEntryData = $this->util->buildChildEntryData($childEntry, $locale);
      $newOutputData['colours'][] = $childEntryData;
    }

    $this->attachFeatureData($newOutputData, $entry, $locale);

    $productUrls = $this->util->getProductUrls($this->getBrand());
    $newOutputData['url'] = isset($productUrls[$entry->partnumber]) ? $productUrls[$entry->partnumber] : null;


    /*
     * Attach sales feature data
     */
    $newOutputData['salesFeatures'] = [];
    foreach ($salesFeatureGroup->getDescriptiveAttributes(null, $locale) as $localizedSalesFeature) {
      $newSalesFeatureData = [
        // Check if it's a qualified feature and put in the association
        'featureKey' => $this->util->getFeatureKeyForSalesFeature($localizedSalesFeature, $brand, $newOutputData['appliance']),
        'top3' => ($localizedSalesFeature->valuesequence <= 3), // double check using field for this purpose - is it same as sequence?
        'headline' => $localizedSalesFeature->valueidentifier,
        'description' => $localizedSalesFeature->noteinfo,
      ];

      $newOutputData['salesFeatures'][] = $newSalesFeatureData;
    }

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
    $data['name'] = (string) $localeRecord->name;
    $data['description'] = (string) $localeRecord->longdescription;
  }

  /**
   * @return void
   */
  abstract protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale);

  /**
   * @return string
   */
  abstract protected function getBrand();
}
