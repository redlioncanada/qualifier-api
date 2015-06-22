<?php

namespace Rlc\Wpq\FeedEntity;

/**
 * Like {@see Simple}, but for an entity described by >1 <record>, e.g. that
 * has 1 record for each locale.
 */
abstract class AbstractCompoundRecord {

  /**
   * Should have some meaningful string key, i.e. locale
   * 
   * @var \SimpleXMLElement[]
   */
  private $records = [];

  /**
   * @var string
   */
  private $defaultKey;

  public function __construct($defaultKey) {
    $this->defaultKey = $defaultKey;
  }

  public function initRecord(\SimpleXMLElement $record, $key) {
    $this->records[$key] = $record;
  }

  public function __get($name) {
    return $this->getRecord()->$name;
  }

  /**
   * @param string $key OPTIONAL
   * @return mixed
   */
  public function getRecord($key = null) {
    if (!isset($key)) {
      $key = $this->defaultKey;
    }
    if (!array_key_exists($key, $this->records)) {
      // Should this raise an exception or be more accomodating? Data might be
      // unreliable.
      throw new \Exception("No record for key '$key'");
    }
    return $this->records[$key];
  }
  
  /**
   * Array of valid arguments to getRecord()
   * 
   * @return string[]
   */
  public function getRecordKeys() {
    return array_keys($this->records);
  }

}
