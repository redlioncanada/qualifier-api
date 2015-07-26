<?php

/*
 * Outputs CSV of products with "no image available" image
 */

$file = __DIR__ . '/../../data/json-responses/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

fputcsv(STDOUT, ["App category", "App sub-category", "SKU", "Image URL"]);
foreach ($resp->products as $p) {
  if (strpos($p->image, 'No Image Available') !== false) {
    fputcsv(STDOUT, [
      $p->appliance,
      (isset($p->type) ? $p->type : ''),
      $p->sku,
      $p->image,
    ]);
  }
}
