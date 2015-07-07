<?php

namespace Lrr;

class Translator {

  private $strings;
  private $defaultLocale;

  public function __construct($strings, $defaultLocale) {
    $this->strings = $strings;
    $this->defaultLocale = $defaultLocale;
  }

  public function setDefaultLocale($defaultLocale) {
    $this->defaultLocale = $defaultLocale;
    return $this;
  }

  public function translate($stringKey, $locale = null) {
    if (!isset($this->strings[$stringKey])) {
      throw new \InvalidArgumentException("No such string key: $stringKey");
    }
    if (!isset($locale)) {
      $locale = $this->defaultLocale;
    }
    if (isset($this->strings[$stringKey][$locale])) {
      return $this->strings[$stringKey][$locale];
    } elseif (isset($this->strings[$stringKey][$this->defaultLocale])) {
      return $this->strings[$stringKey][$this->defaultLocale];
    } else {
      return $stringKey;
    }
  }

  public function __get($stringKey) {
    return $this->translate($stringKey);
  }

}
