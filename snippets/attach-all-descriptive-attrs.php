<?php

/*
 * For use in developing extraction logic for new category:
 * attaches all descriptive attributes to output json under 'descr-attrs'
 * property, to make it easy to search through them.
 * 
 * Comment out all categories in JsonBuilder other than the one being worked on
 * to make this easier.
 */

foreach ($entry->getDescriptiveAttributeGroups() as $grpName => $grp) {
  if (in_array($grpName, ['Endeca', 'EndecaProps'])) {
    continue;
  }
  foreach ($grp->getDescriptiveAttributes() as $attr) {
    $entryData['descr-attrs'][$grpName][] = [
      'description' => $attr->description,
      'valueidentifier' => $attr->valueidentifier,
      'value' => $attr->value,
      'noteinfo' => $attr->noteinfo,
    ];
  }
}