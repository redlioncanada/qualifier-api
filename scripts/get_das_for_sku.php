<?php

/**
 * Get descriptive attributes for SKU
 */

$sku = $argv[1];

$fileContents = file_get_contents(__DIR__ . '/../data/source-xml/MTG_CA_DescriptiveAttribute.xml');
// Match from partnumber tag to last tag in <record/> block
preg_match_all('@(<partnumber>' . $sku . '</partnumber>.*?)</record>@s', $fileContents, $matches);
$output = implode("\n---\n", $matches[1]);
file_put_contents($sku . '-DescriptiveAttributes.xml', $output);