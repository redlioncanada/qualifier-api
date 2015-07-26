<?php

/*
 * Outputs CSV of products where first 7 chars of main sku matches another
 * product - probably these are just colour variants mis-entered as separate
 * products.
 */

$file = __DIR__ . '/../../data/json-responses/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

// Keyed by first 7 chars of sku
$results = [];

foreach ($resp->products as $p) {
  if (!isset($p->sku)) {
    // Don't handle laundry, only 6 of them anyway
    continue;
  }
  $skuPrefix = preg_replace("/([a-z]{3,4}[a-z0-9]{4})[a-z]{2,3}-NAR/i", '$1', $p->sku);
  if (!isset($results[$skuPrefix])) {
    // Init entry to false, any entries that stay false will be removed later
    // with array_filter()
    $results[$skuPrefix] = false;
  } elseif (false === $results[$skuPrefix]) {
    // This is the first dupe, init result row
    $results[$skuPrefix][0] = $p->appliance;
    $results[$skuPrefix][1] = (isset($p->type) ? $p->type : '');
    $results[$skuPrefix][2] = $skuPrefix;
    $results[$skuPrefix][3] = $p->name;
    $results[$skuPrefix][4] = 2;
  } else {
    // Result row already exists; just incr count
    $results[$skuPrefix][4] ++;
  }
}

$results = array_filter($results);

fputcsv(STDOUT, ["App category", "App sub-category", "SKU", "Name", "Number of records"]);
foreach ($results as $r) {
  fputcsv(STDOUT, $r);
}
