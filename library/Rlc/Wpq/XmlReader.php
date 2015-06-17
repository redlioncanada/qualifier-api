<?php

namespace Rlc\Wpq;

class XmlReader {

  private $dataPath;

  function __construct($dataPath) {
    $this->dataPath = $dataPath;
  }

  /**
   * @param string $file
   * @return SimpleXMLElement
   */
  public function readFile($file) {
    // TODO brand selection logic - I think should come from controller,
    // unless I want to try reading from request params in bootstrap
    return simplexml_load_file($this->dataPath . '/MTG_CA_' . $file . '.xml');
  }

}
