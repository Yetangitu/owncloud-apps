#!/bin/sh
#
# creates dist files for all updated apps

ls */appinfo/info.xml|while read f;do
	app=$(echo $f|cut -d '/' -f 1)
	version=$(xml2json < $f|jq -r '.info.version["$t"]')
	if [ ! -f dist/${app}-${version}.tar.gz ]; then
		find ${app} -type f -print0|tar cvzf dist/${app}-${version}.tar.gz --null -T -
	else
		echo ${app} version ${version} up to date
	fi
done
