<?php

// Utility script for development - inspect feed data

$file = __DIR__ . '/../data/json-responses/staging_pretty/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

$sorted_skus = [];
$products_by_sku = [];
foreach ($resp->products as $p) {
  $sku = isset($p->sku) ? $p->sku : $p->washerSku;
  $products_by_sku[$sku] = $p;
  $sorted_skus[] = $sku;
}
sort($sorted_skus);

foreach ($sorted_skus as $sku) {
  $prod_vars = get_object_vars($products_by_sku[$sku]);
  recur_ksort($prod_vars);
  echo var_export($prod_vars) . "\n";
  echo "-----------------------\n";
}






// =================================================

function recur_ksort(&$array) {
   foreach ($array as &$value) {
      if (is_object($value))  $value = get_object_vars($value);
      if (is_array($value))   recur_ksort($value);
   }
   return ksort($array);
}
