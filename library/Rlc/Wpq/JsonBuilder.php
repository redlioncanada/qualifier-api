<?php

namespace Rlc\Wpq;

use Lrr\ServiceLocator;

class JsonBuilder {

  /**
   * @var FeedModelBuilderInterface
   */
  private $feedModelBuilder;

  public function __construct(FeedModelBuilderInterface $feedModelBuilder) {
    $this->feedModelBuilder = $feedModelBuilder;
  }

  /**
   * 
   * @param string $brand
   * @return string JSON
   */
  public function build($brand) {
    $topLevelEntries = $this->feedModelBuilder->buildFeedModel($brand);

    // Just testing code here, will actually return all entries in all groups.
    // For now I filter for an arbitrary group.
    $targetGroupId = 'SC_Kitchen_Cooking';

    $getGroupId = function ($group) {
      return (string) $group->identifier;
    };

    // Filter for target group - dev only to speed up testing
    foreach ($topLevelEntries as $key => $entry) {
      $allCatalogGroups = $entry->getAllCatalogGroups();
      $allCatalogGroupIds = array_map($getGroupId, $allCatalogGroups);
      if (!in_array($targetGroupId, $allCatalogGroupIds)) {
        unset($topLevelEntries[$key]);
      }
    }

    /*
     * From here on is code that could actually be used for production,
     * not just for testing...
     */

    $outputData = [];
    foreach ($topLevelEntries as $entry) {
      $newOutputData = [
        'sku' => (string) $entry->partnumber,
      ];

      $this->addGroups($newOutputData, $entry);

      $childEntries = $entry->getChildEntries();
      foreach ($childEntries as $childEntry) {
        $this->addChildEntryData($newOutputData, $childEntry);
      }

      $outputData[] = $newOutputData;
    }
//    
    // Just testing output
//    ini_set('xdebug.var_display_max_depth', 5);
//    var_dump($catalogEntries['MEW6527DDQ']->getParentEntry());
//    die;

    $json = json_encode($outputData, JSON_PRETTY_PRINT);
//    die($json);
    return $json;
  }

  public function addGroups(array &$data, FeedEntity\CatalogEntry $entry) {
    $allCatalogGroups = $entry->getAllCatalogGroups();
    foreach ($allCatalogGroups as $catalogGroup) {
      $newGroupData = [
        'id' => (string) $catalogGroup->identifier,
        'name' => [],
      ];
      $nameLocales = $catalogGroup->getRecordKeys();
      foreach ($nameLocales as $nameLocale) {
        $newGroupData['name'][$nameLocale] = (string) $catalogGroup->getRecord($nameLocale)->name;
      }
      $data['groups'][] = $newGroupData;
    }
  }

  public function addChildEntryData(array &$data,
      FeedEntity\CatalogEntry $childEntry) {
    $variantPartNumber = (string) $childEntry->partnumber;

    $colourDa = $childEntry->getDefiningAttributeValue('Color');
    $newColoursElem = [
      'sku' => $variantPartNumber,
      'colourCode' => (string) $colourDa->valueidentifier,
      'colourName' => [],
      'prices' => [],
    ];
    $colourLocales = $colourDa->getRecordKeys();
    foreach ($colourLocales as $colourLocale) {
      $newColoursElem['colourName'][$colourLocale] = (string) $colourDa->getRecord($colourLocale)->value;
    }
    
    $prices = $childEntry->getPrices();
    $curDate = new \DateTime();
    foreach ($prices as $price) {
      // TODO published=0 or price=0 should exclude the whole product,
      // not only the price data. not sure about being out of date range,
      // need to find that out.

      if (// conditions to exclude:
          ('1' != $price->published) // ... not published
          // ... price is zero
          || (0 == (float) $price->listprice && 0 == (float) $price->saleprice)
      ) {
        continue;
      }
      // we're not in the date range
      $startDate = new \DateTime((string) $price->startdate);
      $endDate = new \DateTime((string) $price->enddate);
      if (($startDate > $curDate) || ($endDate < $curDate)) {
        continue;
      }

      $newColoursElem['prices'][] = [
        'currency' => (string) $price->currency,
        'list' => (string) $price->listprice,
        'sale' => (string) $price->saleprice,
      ];
    }

    $data['colours'][] = $newColoursElem;
  }

  
  
}
