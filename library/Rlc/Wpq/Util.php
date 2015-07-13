<?php

namespace Rlc\Wpq;

class Util {

  /**
   * Like native trim(), but also takes care of non-breaking spaces
   * (\xA0) which sometimes appear in XML values.
   * 
   * @param string $s
   * @return string
   */
  public function trim($s) {
    $nbsp = json_decode('"\u00a0"');
    // Replace with normal space
    $s = str_replace($nbsp, ' ', $s);
    return trim($s);
  }

}
