<?php

// Utility script for development - inspect feed data

$file = __DIR__ . '/../data/json-responses/maytag-en_CA.json';
$resp = json_decode(file_get_contents($file));

foreach ($resp->products as $p) {
  if (isset($p->sku)) {
    echo "$p->sku\n";
  } else {
    echo "$p->washerSku\n";
    echo "$p->dryerSku\n";
  }
}
