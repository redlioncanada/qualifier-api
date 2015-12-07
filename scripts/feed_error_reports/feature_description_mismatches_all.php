<?php

/*
 * Outputs CSV of feature descriptions that are not consistent per feature headline/product category
 */

$file = __DIR__ . '/../../data/json-responses/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

$list = [];

foreach ($resp->products as $p) {
  if (isset($p->sku)) {
    $sku = $p->sku;
  } else {
    $sku = "$p->washerSku / $p->dryerSku";
  }
  $type = isset($p->type) ? $p->type : '';

  foreach ($p->salesFeatures as $sf) {
    $description_sanitized = remove_sup_tags($sf->description);
    
    if (!isset($list[$p->appliance][$type][$sf->headline][$description_sanitized])) {
      $list[$p->appliance][$type][$sf->headline][$description_sanitized] = [];
    }
    $list[$p->appliance][$type][$sf->headline][$description_sanitized][] = $sku;
  }
}

fputcsv(STDOUT, ["App category", "App sub-category", "Headline", "Description variant",
  "No. occurences", "SKUs (SKU pairs for laundry)"]);

// Report every element of list that has length > 1
foreach ($list as $app_category => $app_category_val) {
  foreach ($app_category_val as $app_sub_category => $app_sub_category_val) {
    foreach ($app_sub_category_val as $headline => $headline_val) {
      if (count($headline_val) < 2) {
        continue;
      }
      foreach ($headline_val as $description => $skus) {
        fputcsv(STDOUT, [$app_category, $app_sub_category, $headline, $description,
          count($skus), implode(", ", $skus)]);
      }
    }
  }
}

function remove_sup_tags($string) {
  return preg_replace('@<sup>.+?</sup>@i', '', $string);
}
