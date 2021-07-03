#!/bin/bash
# Open up a vimdiff for each file that has been changed in a tab
# @author Johannes Braun
# @package pager
# @date 2019-07-30
tmpfile=$(mktemp)

rsync -ncaie ssh ./ "kfe:/www/" \
	--exclude="/admin" \
	--exclude=".git*" \
	--exclude="/tmp" \
	--exclude="*.swp" \
	--exclude="/src" \
	--exclude="/deploy-backups" \
	--exclude="/fetch-backups" \
	--exclude="/node_modules" \
	--exclude="deploy.sh" \
	--exclude="diffchanges.sh" \
	--exclude="package*" \
	--exclude="Session.vim" \
	--exclude="/media" | grep -e '^[^.]' | cut -d' ' -f 2 > "${tmpfile}"

if [ ! -s ${tmpfile} ] ; then
	printf "No differences\n"
	exit 0;
fi

cat "${tmpfile}"
echo -n "Do you want to edit the file list? [a/y/N]"
read -r -n 1 answer 
if [[ ${answer} == 'y' ]] ; then
	"${EDITOR}" "${tmpfile}"
fi
if [[ ${answer} == 'a' ]] ; then
	printf "\naborted\n"
	exit;
fi

vim -p $(cat "$tmpfile")  -c "tabdo vertical diffsplit scp://kfe//www/% | wincmd h | 1" -c "tabmove 0"
