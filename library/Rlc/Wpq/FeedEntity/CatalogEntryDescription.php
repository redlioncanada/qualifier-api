<?php

namespace Rlc\Wpq\FeedEntity;

/**
 * Even though these are per-locale, they are purely locale-specific content,
 * so we treat each <record> as its own "Simple" entity, rather than all locales
 * together as a "Compound" entity.
 */
class CatalogEntryDescription extends AbstractSimpleRecord {
  
}
