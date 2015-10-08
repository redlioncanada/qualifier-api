<?php

namespace Rlc\Wpq\FeedEntity;

class CatalogGroup extends AbstractCompoundRecord {

  /**
   * @var CatalogGroup
   */
  private $parent;

  /**
   * @var CatalogGroup[]
   */
  private $children;

  public function getParent() {
    return $this->parent;
  }

  public function getChildren() {
    return $this->children;
  }

  public function setParent(CatalogGroup $parent) {
    $this->parent = $parent;
  }

  public function addChild(CatalogGroup $child) {
    $this->children[] = $child;
  }

  /**
   * Get all parents recursively as flat array, keyed by group ID.
   * 
   * @return CatalogGroup[]
   */
  public function getAncestors() {
    $ancestors = [];
    $current = $this;
    while ($parent = $current->getParent()) {
      // Index by group ID to avoid duplicates
      $ancestors[$parent->identifier] = $parent;
      $current = $parent;
    }
    return $ancestors;
  }

}
