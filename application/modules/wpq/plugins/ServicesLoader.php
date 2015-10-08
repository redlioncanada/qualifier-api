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

    $configPath = realpath(__DIR__ . '/../configs');
    $config = new Zend_Config_Ini($configPath . '/module.ini', APPLICATION_ENV, true);
    $locales = $config->locales->toArray();
    $config->defaultLocale = $locales[0];

    $t11nStrings = (new Zend_Config_Ini($configPath . '/strings.ini'))->toArray();
    $translator = new Lrr\Translator($t11nStrings, $config->defaultLocale);

    // These may be needed by the rest
    $serviceLocator
        ->loadUtil(new Wpq\Util())
        ->loadConfig($config)
        ->loadTranslator($translator)
    ;

    $dataPath = realpath(APPLICATION_PATH . '/../data'); // no trailing slash
    // Where to find XML feed files
    $xmlPath = $dataPath . '/source-xml';
    // Where to save & retrieve responses for API
    $jsonPath = $dataPath . '/json-responses';

    $xmlReader = new Wpq\XmlReaderStandard($xmlPath);
    $feedModelBuilder = new Wpq\FeedModelBuilder($xmlReader);
    $jsonBuilder = new Wpq\JsonBuilder($feedModelBuilder);


    $salesFeatureAssocs = json_decode(file_get_contents($configPath . '/sales-feature-assocs.json'), true);

    $serviceLocator
        ->loadSalesFeatureAssocs($salesFeatureAssocs)
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
        /**
         * @return Wpq\CatalogEntryProcessorInterface
         */
        ->catalogEntryProcessorFactory(function ($classSuffix) {
          static $instances = [];

          if (!isset($instances[$classSuffix])) {
            $fullClassName = '\\Rlc\\Wpq\\CatalogEntryProcessor\\' . $classSuffix;
            $instances[$classSuffix] = new $fullClassName();
          }

          return $instances[$classSuffix];
        })
    ;
  }

}
