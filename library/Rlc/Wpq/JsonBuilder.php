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

    // Build output data - beginning of real production code
    $outputData = [];
    foreach ($topLevelEntries as $entry) {
      $allCatalogGroups = $entry->getAllCatalogGroups();
      $newOutputData = [
        'sku' => (string) $entry->partnumber,
        'groups' => [],
        'colours' => [],
      ];

      foreach ($allCatalogGroups as $catalogGroup) {
        $newGroupData = [
          'id' => $getGroupId($catalogGroup),
          'name' => [],
        ];
        $nameLocales = $catalogGroup->getRecordKeys();
        foreach ($nameLocales as $nameLocale) {
          $newGroupData['name'][$nameLocale] = (string) $catalogGroup->getRecord($nameLocale)->name;
        }
        $newOutputData['groups'][] = $newGroupData;
      }

      $childEntries = $entry->getChildEntries();
      foreach ($childEntries as $childEntry) {
        $variantPartNumber = (string) $childEntry->partnumber;
        $colourDa = $childEntry->getDefiningAttributeValue('Color');
        $newColoursElem = [
          'sku' => $variantPartNumber,
          'colourCode' => (string) $colourDa->valueidentifier,
          'colourName' => [],
        ];
        $colourLocales = $colourDa->getRecordKeys();
        foreach ($colourLocales as $colourLocale) {
          $newColoursElem['colourName'][$colourLocale] = (string) $colourDa->getRecord($colourLocale)->value;
        }
        $newOutputData['colours'][] = $newColoursElem;
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

}
