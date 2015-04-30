<?php

namespace Rlc\Wpq\FeedEntity;

class CatalogGroup extends AbstractCompound {

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
   * Get all parents recursively as flat array.
   * 
   * @return CatalogGroup[]
   */
  public function getAncestors() {
    $ancestors = [];
    $current = $this;
    while ($parent = $current->getParent()) {
      $ancestors[] = $parent;
      $current = $parent;
    }
    return $ancestors;
  }

}
