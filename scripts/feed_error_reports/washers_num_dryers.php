<?php

/*
 * Outputs CSV summary of washer > dryer assocs
 */

$file = __DIR__ . '/../../data/json-responses/whirlpool-en_CA.json';
$resp = json_decode(file_get_contents($file));

fputcsv(STDOUT, ["Washer SKU", "Num. Dryers", "Dryer SKU(s)"]);
foreach ($resp->products as $p) {
  $dryerSkus = [];
  
  foreach ($p->dryers as $d) {
    $dryerSkus[] = $d->sku;
  }
  
  fputcsv(STDOUT, [
    $p->sku,
    $p->numDryers,
    implode(', ', $dryerSkus),
  ]);
}
