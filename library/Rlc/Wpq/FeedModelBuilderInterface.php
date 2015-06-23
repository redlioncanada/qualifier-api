<?php

namespace Rlc\Wpq;

interface FeedModelBuilderInterface {

  /**
   * Get top-level catalog entries with all associated objects filled in
   * 
   * @param string $brand
   * @return FeedEntity\CatalogEntry[]
   */
  public function buildFeedModel($brand);
}
