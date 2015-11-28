<?php

/*
 * Usage example:
 * 
 *   php update-xml.php MTGCA
 * 
 * Using PHP instead of just a bash script in order to reliably work with
 * paths.
 */

if ($argc < 2) {
  echo "Provide XML file prefix as argument \$1\n";
  exit(1);
}

if (!in_array($argv[1], ['MTGCA', 'KADCA', 'WHRCA'])) {
  echo "XML file prefix was not one of the expected values: 'MTGCA', 'KADCA', 'WHRCA'\n";
  exit(1);
}

$xml_dir = realpath(__DIR__ . '/../data/source-xml');
if (!$xml_dir) {
  echo "Can't locate XML directory\n";
  exit(1);
}

ob_start();
$retval = null;
$brand_prefix = escapeshellarg($argv[1]);
$xml_dir = escapeshellarg($xml_dir);
system('wget -O ' . $xml_dir . '"/xml_"'
    . $brand_prefix
    . '".zip" "http://access.whirlpool.com/mr/getMediaType.do?mediaType="'
    . $brand_prefix
    . '"&sku=IBM_Extract"', $retval
);
if (0 < $retval) {
  echo "wget failed:\n";
  ob_end_flush();
  exit(1);
}

system('unzip ' . $xml_dir . '"/xml_"' . $brand_prefix . '".zip" '
    . '-d ' . $xml_dir, $retval);
if (0 < $retval) {
  echo "unzip failed:\n";
  ob_end_flush();
  exit(1);
}

system("mv $xml_dir'/'$brand_prefix'/*.xml' $xml_dir", $retval);
if (0 < $retval) {
  echo "mv $xml_dir'/'$brand_prefix'/*.xml' $xml_dir failed:\n";
  ob_end_flush();
  exit(1);
}

system("rm -rf $xml_dir'/'$brand_prefix'/'", $retval);
if (0 < $retval) {
  echo "rm -rf $xml_dir'/'$brand_prefix'/' failed:\n";
  ob_end_flush();
  exit(1);
}

system("rm 'xml_'$brand_prefix'.zip'", $retval);
if (0 < $retval) {
  echo "rm 'xml_'$brand_prefix'.zip' failed:\n";
  ob_end_flush();
  exit(1);
}
