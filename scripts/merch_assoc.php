<?php

// Utility script for development - inspect feed data

/**
 *
 * For analysing the *_CA_MerchandisingAssociation.xml files.
 *
 */


$x = simplexml_load_file(__DIR__ . '/../data/source-xml/MTG_CA_MerchandisingAssociation.xml');

foreach ($x->record as $r) {
  if ('X-SELL' !== (string) $r->type) continue;
  print_r(get_object_vars($r));
  echo "\n====================================\n";
}
