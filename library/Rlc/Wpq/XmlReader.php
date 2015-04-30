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
    return simplexml_load_file($this->dataPath . '/MTG_CA_' . $file . '.xml');
  }

}
