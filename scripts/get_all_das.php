<?php

// Utility script for development - inspect feed data

$x = simplexml_load_file(__DIR__ . '/../data/source-xml/MTG_CA_DescriptiveAttribute.xml');

foreach ($x->record as $r) {
  if ('Miscellaneous' !== (string) $r->groupname) continue;
  if ('Disclaimer' !== (string) $r->description) continue;
  if ('en_CA' !== (string) $r->locale) continue;
  echo $r->partnumber . "\n"
      . 'noteinfo:' . $r->noteinfo . "\n"
      . 'description:' . $r->description . "\n"
      . 'valueidentifier:' . $r->valueidentifier . "\n"
      . 'sequence:' . $r->sequence . "\n"
      . 'value:' . $r->value . "\n"
      . "====================================\n";
}
