# To get setup:

## Install composer and tell it to install dependencies.

    php -r "readfile('https://getcomposer.org/installer');" | php
    ./composer.phar install

## Download XML for maytag:

    cd data/source-xml
    wget -O xml.zip "http://access.whirlpool.com/mr/getMediaType.do?mediaType=MTGCA&sku=IBM_Extract"
    unzip xml.zip *.xml
    mv MTGCA/*.xml .
    rm -rf MTGCA/

## Start the server:

    APPLICATION_ENV=staging php -S localhost:1337 -t public/ public/index.php

Then build the JSON by requesting http://localhost:1337/wpq/feed-processor/

Now the API is ready: http://localhost:1337/wpq/product-list/index/brand/maytag/locale/en_CA