<?php

/*
 * For each product, look at all sales features, and output any pairs of
 * sales features for the same product where the names are too similar.
 * 
 * (Example of what this catches: single product with a
 * "17,000 BTU Power Burner" sales feature and another with name
 * "15,000 BTU Power Burner".)
 */

$file = __DIR__ . '/../../data/json-responses/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

$results = [];

foreach ($resp->products as $p) {
  $featureList = [];
  foreach ($p->salesFeatures as $k => $sf) {
    $featureList[$k] = $sf->headline . $sf->description;
  }

  // Compare all combinations and flag any that are too similar
  $compResults = [];
  foreach ($featureList as $k => $s) {
    foreach ($featureList as $k2 => $s2) {
      if ($k != $k2) {
        $compoundKey = [$k, $k2];
        sort($compoundKey);
        $compoundKey = implode('-', $compoundKey);
        if (!isset($compResults[$compoundKey])) {
          similar_text($s, $s2, $compResults[$compoundKey]);
        }
      }
    }
  }

  foreach ($compResults as $compoundKey => $score) {
    if ($score > 90) {
      list ($k, $k2) = explode('-', $compoundKey);
      $results[] = [
        $p->appliance,
        isset($p->type) ? $p->type : '',
        isset($p->sku) ? $p->sku : "$p->washerSku / $p->dryerSku",
        $p->salesFeatures[$k]->headline,
        $p->salesFeatures[$k]->description,
        $p->salesFeatures[$k2]->headline,
        $p->salesFeatures[$k2]->description,
      ];
    }
  }
}

fputcsv(STDOUT, ["App category", "App sub-category", "SKU",
  "Feature 1 headline", "Feature 1 description",
  "Feature 2 headline", "Feature 2 description",
]);
foreach ($results as $r) {
  fputcsv(STDOUT, $r);
}
