<?php

namespace Rlc\Wpq\FeedEntity;

/**
 * It may appear that this should be a compound record with currency as the
 * key, but price records also have startdate and enddate, as well as
 * published (boolean), which makes me think that, although the partnumbers
 * in my working copy of the feed are unique in this file, that might not always
 * be the case. It might just contain a whole bunch of prices for a given
 * product, some of which are expired or queued for the future, others not
 * published, etc. Best to just represent it as a flat list and let the client
 * code decide what to take from it. -LR
 */
class Price extends AbstractSimpleRecord {
  
}
