<?php

/*
 * Outputs CSV of products where a colour is represented more than once among its variants.
 */

$file = __DIR__ . '/../../data/json-responses/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

fputcsv(STDOUT, ["App category", "App sub-category", "SKU", "Duplicated colour"]);

foreach ($resp->products as $p) {
  if (isset($p->sku)) {
    $sku = $p->sku;
    $colourCodeFieldName = 'colourCode';
    $colourNameFieldName = 'colourName';
  } else {
    $sku = "$p->washerSku / $p->dryerSku";
    $colourCodeFieldName = 'code';
    $colourNameFieldName = 'name';
  }

  $coloursSoFar = []; // code => name
  foreach ($p->colours as $colour) {
    $code = $colour->$colourCodeFieldName;
    $name = $colour->$colourNameFieldName;
    if (isset($coloursSoFar[$code])) {
      fputcsv(STDOUT, [$p->appliance, $p->type, $sku, "$code / $name"]);
    } else {
      $coloursSoFar[$code] = $name;
    }
  }
}
