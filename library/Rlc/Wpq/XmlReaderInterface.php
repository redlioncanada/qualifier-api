<?php

namespace Rlc\Wpq;

interface XmlReaderInterface {

  /**
   * @param string $brand
   * @param string $file
   * @return SimpleXMLElement
   */
  public function readFile($brand, $file);
}
