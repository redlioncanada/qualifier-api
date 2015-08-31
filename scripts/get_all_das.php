<?php

$x = simplexml_load_file(__DIR__ . '/../data/source-xml/WP_CA_DescriptiveAttribute.xml');

foreach ($x->record as $r) {
  // if ('CompareFeature' !== (string) $r->groupname) continue;
  if ('en_CA' !== (string) $r->locale) continue;
  echo $r->partnumber . "\n"
      . $r->noteinfo . "\n"
      . $r->description . "\n"
      . $r->valueidentifier . "\n"
      . $r->value . "\n"
      . "====================================\n";
}