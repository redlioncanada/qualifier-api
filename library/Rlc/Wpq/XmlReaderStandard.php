<?php

namespace Rlc\Wpq;

class XmlReaderStandard implements XmlReaderInterface {

  /**
   * @var string
   */
  private $dataPath;

  /**
   * @var array
   */
  private $filePrefixesByBrand = [
    'maytag' => 'MTG_CA_',
    'kitchenaid' => 'KAD_CA_',
    'whirlpool' => 'WP_CA_',
  ];

  function __construct($dataPath) {
    $this->dataPath = $dataPath;
  }

  /**
   * @param string $brand
   * @param string $file
   * @return SimpleXMLElement
   */
  public function readFile($brand, $file) {
    if (!array_key_exists($brand, $this->filePrefixesByBrand)) {
      throw new \InvalidArgumentException("Invalid brand: $brand");
    }
    return simplexml_load_file($this->dataPath . '/'
        . $this->filePrefixesByBrand[$brand]
        . $file . '.xml');
  }

}
