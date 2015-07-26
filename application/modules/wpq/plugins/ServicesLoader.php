<?php

use Lrr\ServiceLocator,
    Rlc\Wpq,
    Rlc\Wpq\FeedEntity;

/**
 * Loading services in this plugin on preDispatch rather than in bootstrap
 * is a good practice, causing this to only happen if this module is being
 * dispatched to -- whereas all module bootstraps are always run at an earlier
 * stage, before routing.
 */
class Wpq_Plugin_ServicesLoader extends Zend_Controller_Plugin_Abstract {

  public function preDispatch(\Zend_Controller_Request_Abstract $request) {
    $serviceLocator = new ServiceLocator();
    ServiceLocator::load($serviceLocator);

    $dataPath = realpath(APPLICATION_PATH . '/../data'); // no trailing slash
    // Where to find XML feed files
    $xmlPath = $dataPath . '/source-xml';
    // Where to save & retrieve responses for API
    $jsonPath = $dataPath . '/json-responses';

    $xmlReader = new Wpq\XmlReaderStandard($xmlPath);
    $feedModelBuilder = new Wpq\FeedModelBuilder($xmlReader);
    $jsonBuilder = new Wpq\JsonBuilder($feedModelBuilder);

    $configPath = realpath(__DIR__ . '/../configs');
    $config = new Zend_Config_Ini($configPath . '/module.ini', APPLICATION_ENV, true);
    $locales = $config->locales->toArray();
    $config->defaultLocale = $locales[0];

    $t11nStrings = json_decode(file_get_contents($configPath . '/strings.json'), true);
    $translator = new Lrr\Translator($t11nStrings, $config->defaultLocale);

    $salesFeatureAssocs = json_decode(file_get_contents($configPath . '/sales-feature-assocs.json'), true);

    $serviceLocator
        ->loadConfig($config)
        ->loadTranslator($translator)
        ->loadSalesFeatureAssocs($salesFeatureAssocs)
        ->loadUtil(new Wpq\Util())
        ->loadJsonFileManager(new Wpq\JsonFileManager($jsonPath, $jsonBuilder))
        ->catalogEntryFactory(function(\SimpleXMLElement $record) {
          return new FeedEntity\CatalogEntry($record);
        })
        ->catalogGroupFactory(function () use ($config) {
          return new FeedEntity\CatalogGroup($config->defaultLocale);
        })
        ->catalogEntryDescriptionFactory(function () use ($config) {
          return new FeedEntity\CatalogEntryDescription($config->defaultLocale);
        })
        ->priceFactory(function (\SimpleXMLElement $record) {
          return new FeedEntity\Price($record);
        })
        ->definingAttributeValueFactory(function () use ($config) {
          return new FeedEntity\DefiningAttributeValue($config->defaultLocale);
        })
        ->descriptiveAttributeGroupFactory(function () use ($config) {
          return new FeedEntity\DescriptiveAttributeGroup($config->defaultLocale);
        })
        ->descriptiveAttributeFactory(function (\SimpleXMLElement $record) {
          return new FeedEntity\DescriptiveAttribute($record);
        })
    ;
  }

}
