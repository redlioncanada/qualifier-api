<?php

namespace Rlc\Wpq;

interface XmlReaderInterface {

  /**
   * @param string $file
   * @return SimpleXMLElement
   */
  public function readFile($file);
}
