<?php

namespace Rlc\Wpq\FeedEntity;

use Lrr\ServiceLocator;

/**
 * Pseudo-subclass wrapper for \SimpleXMLElement. Can't extend because no way to
 * control what class is returned from simplexml_read_file(), but objects of
 * this class can wrap \SimpleXMLElement objects and provide transparent access
 * to their methods and pseudo-properties.
 * 
 * JsonBuilder is still aware of the entire XML schema. These objects don't
 * encapsulate the schema, just provide convenient access to the data and
 * entity relations.
 */
abstract class AbstractSimpleRecord {

  /**
   * @var \SimpleXMLElement
   */
  private $record;

  public function __construct(\SimpleXMLElement $record) {
    $this->record = $record;
  }

  public function __call($name, $arguments) {
    return call_user_func_array([$this->record, $name], $arguments);
  }

  public function __get($name) {
    return ServiceLocator::util()->trim((string) $this->record->$name);
  }

}
