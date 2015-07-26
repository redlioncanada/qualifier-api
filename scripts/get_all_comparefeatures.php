<?php

$x = simplexml_load_file('MTG_CA_DescriptiveAttribute.xml');

foreach ($x->record as $r) {
  if ('CompareFeature' !== (string) $r->groupname) continue;
  echo $r->partnumber . '-'
      . $r->groupname . '-'
      . $r->locale . '-'
      . $r->sequence . '-'
      . $r->valuesequence
      . "\n";
}