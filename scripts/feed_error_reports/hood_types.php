<?php

/*
 * Checks that the hood types I'm aware of cover all Hoods
 */

$file = __DIR__ . '/../../data/json-responses/whirlpool-en_CA.json';
$resp = json_decode(file_get_contents($file));

fputcsv(STDOUT, ["SKU"]);
foreach ($resp->products as $p) {
  if (!(
    $p->islandMount ||
    $p->wallMount ||
    $p->underCabinet ||
    $p->customHoodLiner ||
    $p->inLineBlower
  )) {
    fputcsv(STDOUT, [$p->sku]);
  }
}
