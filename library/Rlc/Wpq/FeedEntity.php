<?php

namespace Rlc\Wpq;

/**
 * Pseudo-subclass wrapper for \SimpleXMLElement
 */
class FeedEntity {

  /**
   * @var \SimpleXMLElement
   */
  private $simpleXmlElement;

  /**
   * @var FeedEntity
   */
  private $parent;

  /**
   * @var FeedEntity[]
   */
  private $children = [];

  /**
   * Stores many-to-many assocs, e.g. catalog entries to catalog groups
   * 
   * @var FeedEntity[]
   */
  private $assocs = [];

  public function __construct(\SimpleXMLElement $simpleXmlElement) {
    $this->simpleXmlElement = $simpleXmlElement;
  }

  /**
   * @return FeedEntity
   */
  function getParent() {
    return $this->parent;
  }

  /**
   * @return FeedEntity[]
   */
  function getChildren() {
    return $this->children;
  }

  /**
   * @return FeedEntity[]
   */
  public function getAssocs() {
    return $this->assocs;
  }

  function setParent(FeedEntity $parent) {
    $this->parent = $parent;
  }

  function addChild(FeedEntity $child) {
    $this->children[] = $child;
  }

  /**
   * Add many-to-many association, e.g. catalog entries to catalog groups
   * 
   * @param \Rlc\Wpq\FeedEntity $assoc
   */
  public function addAssoc(FeedEntity $assoc) {
    $this->assocs[] = $assoc;
  }

  public function __call($name, $arguments) {
    return call_user_func_array([$this->simpleXmlElement, $name], $arguments);
  }

  public function __get($name) {
    return $this->simpleXmlElement->$name;
  }

}
