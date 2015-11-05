<?php

namespace Rlc\Wpq;

interface CatalogEntryProcessorInterface {

  /**
   * Process a CatalogEntry object into data and add it to the output array.
   * 
   * @param \Rlc\Wpq\FeedEntity\CatalogEntry $entry
   * @param array $entries \Rlc\Wpq\FeedEntity\CatalogEntry[]
   * @param string $locale
   * @param array $outputData REFERENCE
   * 
   * @return void
   */
  public function process(FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData);
}
