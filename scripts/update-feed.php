// <?php

/**
 * Downloads new XML and runs feed processor job. Using PHP wrapper for bash
 * script to reliably work get current path.
 * 
 * Developed as a daily cron job. If you direct stdout to a file, this script
 * will only give output (stderr) when something goes wrong.
 * 
 * Usage:
 *   php update-feed.php <docroot_url>
 * 
 * docroot_url is the URL corresponding to the 'public' folder of the Zend
 * Application. No trailing slash necessary.
 * 
 * Usage example for staging server:
 *   php update-feed.php http://mymaytag.wpc-stage.com/api/public >> cron-log.txt
 * 
 * Depends on update-xml.sh being in the same directory and executable, and
 * that script in turn depends on 'wget' and 'unzip' utilities on the system.
 */
if ($argc < 2) {
  fwrite(STDERR, "Provide URL to Zend App document root as argument \$1\n");
  exit(1);
}

if (!preg_match('@https?://@', $argv[1])) {
  fwrite(STDERR, "URL to Zend App document root should be absolute, beginning with either http:// or https://\n");
  exit(1);
}

// Use this time for both output and backup dir name so they can be matched
$time = time();

echo "Beginning update-feed.php\n"
 . date('r', $time) . "\n";

$xml_dir = realpath(__DIR__ . '/../data/source-xml');
$json_dir = realpath(__DIR__ . '/../data/json-responses');

// Download, extract & overwrite XML files for all 3 brands
foreach (['MTGCA', 'KADCA', 'WHRCA'] as $brand_prefix) {
  echo "Downloading and extracting $brand_prefix feed\n";
  cmd_wrapper(__DIR__ . '/update-xml.sh ' . escapeshellarg($brand_prefix) . ' ' . escapeshellarg($xml_dir));
  echo "\n";
}

$backup_path = $json_dir . '/backup_' . date('Y-m-d_His', $time);
echo "Backing up JSON files before processing new and overwriting\n";
echo "Backup path: $backup_path\n";
cmd_wrapper('mkdir ' . escapeshellarg($backup_path));
cmd_wrapper('cp -p ' . escapeshellarg($json_dir) . '/*.json ' . escapeshellarg($backup_path));

$docroot = rtrim($argv[1], '/');
echo "Requesting: $docroot/wpq/feed-processor\n";
$resp = file_get_contents($docroot . '/wpq/feed-processor');
echo "Response begins from feed-processor\n"
 . "-------------------------------------------------------\n"
 . $resp . "\n"
 . "-------------------------------------------------------\n"
 . "Response ends\n"
 . "update-feed.php complete\n";

// --------------------------------------------------------

function cmd_wrapper($command) {
  $return = 0;
  $output = [];
  exec($command, $output, $return);
  if ($return > 0) {
    fwrite(STDERR, "Command failed: $command\n");
    exit(1);
  }
  echo implode("\n", $output);
}
