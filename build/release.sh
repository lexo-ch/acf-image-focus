#/bin/bash

NEXT_VERSION=$1
CURRENT_VERSION=$(cat composer.json | grep version | head -1 | awk -F= "{ print $2 }" | sed 's/[version:,\",]//g' | tr -d '[[:space:]]')

sed -ie "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEXT_VERSION\"/g" composer.json
rm -rf composer.jsone

sed -ie "s/Version:           $CURRENT_VERSION/Version:           $NEXT_VERSION/g" acf-image-focus.php
rm -rf acf-image-focus.phpe

sed -ie "s/Stable tag: $CURRENT_VERSION/Stable tag: $NEXT_VERSION/g" readme.txt
rm -rf readme.txte

sed -ie "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEXT_VERSION\"/g" info.json
rm -rf info.jsone

sed -ie "s/v$CURRENT_VERSION/v$NEXT_VERSION/g" info.json
rm -rf info.jsone

sed -ie "s/$CURRENT_VERSION.zip/$NEXT_VERSION.zip/g" info.json
rm -rf info.jsone

npx mix --production
sudo composer dump-autoload -oa

mkdir acf-image-focus

cp -r assets acf-image-focus
cp -r languages acf-image-focus
cp -r dist acf-image-focus
cp -r src acf-image-focus
cp -r vendor acf-image-focus

cp ./*.php acf-image-focus
cp LICENSE acf-image-focus
cp readme.txt acf-image-focus
cp README.md acf-image-focus
cp CHANGELOG.md acf-image-focus

zip -r ./build/acf-image-focus-$NEXT_VERSION.zip acf-image-focus -q
