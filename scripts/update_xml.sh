#!/bin/bash
cd ../data/source-xml &&\
  wget -O xml_$1.zip "http://access.whirlpool.com/mr/getMediaType.do?mediaType=$1&sku=IBM_Extract" &&\
  unzip xml_$1.zip &&\
  mv $1/*.xml . &&\
  rm -rf $1/ &&\
  rm xml_$1.zip