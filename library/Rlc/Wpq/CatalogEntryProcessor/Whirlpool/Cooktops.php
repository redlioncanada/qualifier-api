<?php

namespace Rlc\Wpq\CatalogEntryProcessor\Whirlpool;

use Rlc\Wpq,
    Lrr\ServiceLocator;

class Cooktops extends Wpq\CatalogEntryProcessor\StandardAbstract {

  protected function attachFeatureData(array &$entryData,
      Wpq\FeedEntity\CatalogEntry $entry, $locale) {
    $description = $entry->getDescription();
    $salesFeatureGroup = $entry->getDescriptiveAttributeGroup('SalesFeature');
    $compareFeatureGroup = $entry->getDescriptiveAttributeGroup('CompareFeature');
    $imageUrlPrefix = ServiceLocator::config()->imageUrlPrefix;

    /*
     * Name/description-based info - use default locale (English)
     */

    $entryData['induction'] = false !== stripos($description->name, 'induction');
    $entryData['electric'] = !$entryData['induction'] && (false !== stripos($description->name, 'electric'));
    $entryData['gas'] = false !== stripos($description->name, 'gas');

    $entryData['electricEvenHeat'] = (
        $entryData['electric'] &&
        false !== stripos($description->longdescription, "even-heat")
        );

    /*
     * Sales-feature-based info
     */

    // Init all to false
    $entryData['cookShield'] = false;
    $entryData['touchActivated'] = false;
    $entryData['meltAndHold'] = false;
    $entryData['inductionSimmer'] = false;
    $entryData['performanceBoost'] = false;
    $entryData['5KBTUSimmer'] = false;
    $entryData['15KBTU'] = false;
    $entryData['18KBTUEvenHeat'] = false;
    $entryData['20KBTUDual'] = false;

    if ($salesFeatureGroup) {
      $entryData['cookShield'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("CookShield Finish");

      $entryData['touchActivated'] = (
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Touch-Activated Electronic Controls")
          // Try the name/descr
          ||
          preg_match('/Touch[ -]Activated Controls/i', $description->name) ||
          preg_match('/Touch[ -]Activated Controls/i', $description->longdescription)
          );

      $entryData['meltAndHold'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Melt and Hold");

      $entryData['inductionSimmer'] = (
          $entryData['induction'] &&
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Simmer Function")
          );

      $entryData['performanceBoost'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("Performance Boost");

      $entryData['5KBTUSimmer'] = (
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"5K BTU Even-Heat\u2122 Simmer Burner"'))
          // 6K BTU also counts
          ||
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"6K BTU Even-Heat\u2122 Simmer Burner"'))
          );

      $entryData['15KBTU'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifierMatch("15K BTU");
      $entryData['18KBTUEvenHeat'] = $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"18K BTU Even-Heat\u2122 Gas Grill"'));
      $entryData['20KBTUDual'] = (
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier("20K BTU Professional Dual Ring Burner") ||
          $salesFeatureGroup->descriptiveAttributeExistsByValueIdentifier(json_decode('"20K BTU Ultra Power\u2122 Dual-Flame Burner"'))
          );
    }

    /*
     * Compare-feature-based info
     */

    // Init all to false
    $entryData['5Burners'] = false;
    $entryData['6Burners'] = false;

    if ($compareFeatureGroup) {
      $numElementsAttr = $compareFeatureGroup->getDescriptiveAttributeByValueIdentifier("Number of Elements-Burners");
      if ($numElementsAttr) {
        $entryData['5Burners'] = 5 <= $numElementsAttr->value;
        $entryData['6Burners'] = 6 <= $numElementsAttr->value;
      }
    }

    /*
     * Other
     */

    // Add image
    $entryData['image'] = $imageUrlPrefix . $entry->fullimage;

    $this->attachPhysicalDimensionData($entryData, $entry);
  }

  protected function getBrand() {
    return 'whirlpool';
  }

  protected function getCategory() {
    return 'Cooktops';
  }

}
