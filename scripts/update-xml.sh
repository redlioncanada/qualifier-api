#!/bin/bash
#
# Depends on 'wget' and 'unzip'.
#
# See update-feed.php, a complete update/process feed service, which uses this script.
#
# The stderr of each command is routed to stdout so that update-feed.php retains complete control
# of the output -- especially for wget which writes normal status stuff to stderr.
#
# Usage:
#   ./update-xml.sh <brand> <xml_directory>
#
# example usage:
#   ./update-xml.sh MTGCA ../data/source-xml/
#

cd $2 2>&1 &&\
  wget --no-verbose -O xml_$1.zip "http://access.whirlpool.com/mr/getMediaType.do?mediaType=$1&sku=IBM_Extract" 2>&1 &&\
  unzip xml_$1.zip 2>&1 &&\
  mv $1/*.xml . 2>&1 &&\
  rm -rf $1/ 2>&1 &&\
  rm xml_$1.zip 2>&1
