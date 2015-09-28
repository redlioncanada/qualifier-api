<?php

namespace Rlc\Wpq;

interface CatalogEntryProcessorInterface {

  public function process(FeedEntity\CatalogEntry $entry, array $entries,
      $locale, array &$outputData);
}
