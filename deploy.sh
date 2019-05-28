#!/bin/bash
#
# Deploy / Fetch script
#
# @author Johannes Braun <j.braun@agentur-halma.de>
# @package deploy-sh
#
# For a web project (assuming content-o-mat based!)
# according to convention of fetch-deploy-cycle, we
# deploy `dist`, `templates`, `phpfiles` directories
# fetch database, `media` directory
#
# This script helps automating this cycle
#
# Prerequisites:
# - SSH or FTP access to the remote host (SSH is strongly recommended!)
# - Initially uploaded content-o-mat web project including a valid `settings_db.inc` file containing database credentials
# - rsync (for ssh) or lftp (for ftp)
# - for FTP mode: admin/sqldump.php and admin/classes/Mysqldump.php 
#
# How to use
#
# 1. Set-up your project and initially upload all files to the remote host
# 2. For SSH: Setup access by key (Add your public key (ususally ~/.ssh/id_rsa.pub) to the remote's `~/.ssh/authorized_keys`)
#    For FTP: Add FTP credentials to your `~/.netrc` file
# 3. Edit MODE, HOSTNAME, REMOTE_DIR, LOCAL_DIR, DB_SETTINGS_FILE, DEPLOY_DIRS and FETCH_DIRS to suit your project's needs
# 4. Run `./deploy.sh fetch` or `./deploy.sh deploy`
#
# Run `./deploy.sh -h` for a list of available options



# TODO: Add --backup-dir="rsync-backups/$(date +%F-%H%M%S)/" for deploy


set -e

##
# @var string ssh|ftp
# Defines the mode to use, either ssh or ftp
MODE=ssh

##
# @var string
# Hostname to use for either ssh or ftp connection to remote host
HOSTNAME=

##
# @var string
# Username for FTP access. Use `export LFTP_PASSWORD=secret` to set the
# password. You can either do this here (unsecure, not recommended) or on
# command line (recommended)
FTP_USERNAME=

##
# @var string
# Remote base directory path, relative to login path, no leading or trailing slashes!
REMOTE_DIR=

##
# @var string 
# Local base directory path
LOCAL_DIR=$PWD

##
# @var string
# settings_db file (relative to LOCAL_DIR / REMOTE_DIR)
DB_SETTINGS_FILE=admin/settings_db.inc

##
# @var string
# List of directories to deploy (relative to LOCAL_DIR, space separated)
DEPLOY_DIRS="phpincludes templates dist"

##
#  string 			List of directories to fetch
# List of directories to fetch (relative to REMOTE_DIR, space separated)
FETCH_DIRS="media"

##
# @var string
# Options to pass to `rsync` when fetching files
RSYNC_FETCH_OPTIONS="--archive --quiet --checksum --delete --backup --backup-dir=${LOCAL_DIR}/fetch-backups/backup-$(date +%F-%H%M%S)"

##
# @var string
# Options to pass to `rsync` when deploying files
RSYNC_DEPLOY_OPTIONS="--archive --quiet --checksum --delete --backup --backup-dir=${REMOTE_DIR}/deploy-backups/backup-$(date +%F-%H%M%S)"

##
# @var string
# Options to pass to lftp's mirror command when fetching files (see `man lftp`)
FTP_FETCH_OPTIONS="--verbose=0 --delete --continue --recursion=always --overwrite"

##
# @var string
# Options to pass to lftp's mirror command when deploying files (see `man lftp`)
FTP_DEPLOY_OPTIONS="--verbose=0 --delete --continue --recursion=always --overwrite"



#----------------------------------------------------------------------------
# NO NEED TO EDIT ANYTHING BELOW THIS LINE!!
#----------------------------------------------------------------------------



# Make sure we have all involved programs installed
MYSQL=$(which mysql)
MYSQLDUMP=$(which mysqldump)
case $MODE in
	ssh)
		RSYNC=$(which rsync)
		;;
	ftp)
		LFTP=$(which lftp)
		;;
esac

# Some color definitions
BLACK='\033[0;30m'
RED='\033[0;31m'
GREEN='\033[0;32m'
ORANGE='\033[0;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
LIGHTGREY='\033[0;37m'
DARKGREY='\033[1;30m'
LIGHTRED='\033[1;31m'
LIGHTGREEN='\033[1;32m'
YELLOW='\033[1;33m'
LIGHTBLUE='\033[1;34m'
LIGHTPURPLE='\033[1;35m'
LIGHTCYAN='\033[1;36m'
WHITE='\033[1;37m'
NC='\033[0m'

# Run-time parameters

##
# @var integer
# Whether to fetch database or not
FETCH_DB=0

##
# @var integer
# Whether to fetch files
FETCH_FILES=0
##
# @var integer
# Interactive mode (ask what to fetch, if 0 then fetches db and files)
INTERACTIVE=1
##
# @var integer
# Wet / dry run. If set to 1, no files will be transferred actually and no database dump will be written.
DRY=0



function usage {
	printf "Usage: $0 [OPTIONS] fetch|deploy\n\n"
	printf "Options:\n"
	printf -- "-y\t\tNon-interactive mode, assume [Y]es to all questions\n"
	printf -- "-n\t\tDry mode\n"
	printf -- "-h\t\tPrint this help message"
}



function fetch {

	if [[ ${INTERACTIVE} == 0 ]] ; then
		FETCH_DB=1
		FETCH_FILES=1
	else
		printf "${YELLOW}Fetch database? [Y/n]${NC}\n"
		read -s -n 1 a
		if [[ -z $a ]] || [[ $a == "y" ]] || [[ $a == "Y" ]] ; then
			FETCH_DB=1
		fi

		printf "\n${YELLOW}Fetch media files? [Y/n]${NC}\n"
		read -s -n 1 a
		if [[ -z $a ]] || [[ $a == "y" ]] || [[ $a == "Y" ]] ; then
			FETCH_FILES=1
		fi
	fi

	if [[ ${FETCH_DB} == 1 ]] ; then

		# Detect local db login credentials
		if [[ ! -e ${DB_SETTINGS_FILE} ]] ; then
			printf "No local db settings file found (${DB_SETTINGS_FILE})"
			exit -1
		fi

		LOCAL_DB_HOST=$(echo "@include \"${DB_SETTINGS_FILE}\"; echo \$databases['default']['server'];" | php -a | tail -n 1)
		LOCAL_DB_USER=$(echo "@include \"${DB_SETTINGS_FILE}\"; echo \$databases['default']['user'];" | php -a | tail -n 1)
		LOCAL_DB_NAME=$(echo "@include \"${DB_SETTINGS_FILE}\"; echo \$databases['default']['db'];" | php -a | tail -n 1)
		LOCAL_DB_PASS=$(echo "@include \"${DB_SETTINGS_FILE}\"; echo \$databases['default']['pw'];" | php -a | tail -n 1)
		# printf "%s\n%s\n%s\n%s\n\n" $LOCAL_DB_HOST $LOCAL_DB_USER $LOCAL_DB_NAME $LOCAL_DB_PASS

		# Detect remote db login credentials
		tmpfile=$(mktemp)
		case ${MODE} in
			ssh)
				scp ${HOSTNAME}:${REMOTE_DIR}/${DB_SETTINGS_FILE} ${tmpfile}
				;;

			ftp)
				echo "get -e ${REMOTE_DIR}/${DB_SETTINGS_FILE} -o ${tmpfile}" | lftp ${HOSTNAME} --user "${FTP_USERNAME}" --env-password > /dev/null
				;;
		esac

		if [[ $? -ne 0 || ! -e ${tmpfile} ]] ; then
			printf "Failed to download settings_db.inc file from remote"
			exit -1
		fi

		REMOTE_DB_HOST=$(echo "@include '${tmpfile}'; echo \$databases['default']['server'];" | php -a | tail -n 1)
		REMOTE_DB_USER=$(echo "@include '${tmpfile}'; echo \$databases['default']['user'];" | php -a | tail -n 1)
		REMOTE_DB_NAME=$(echo "@include '${tmpfile}'; echo \$databases['default']['db'];" | php -a | tail -n 1)
		REMOTE_DB_PASS=$(echo "@include '${tmpfile}'; echo \$databases['default']['pw'];" | php -a | tail -n 1)

		# printf "%s\n%s\n%s\n%s\n\n" $REMOTE_DB_HOST $REMOTE_DB_USER $REMOTE_DB_NAME $REMOTE_DB_PASS
		rm -f "${tmpfile}"

		# Make db backup

		BACKUP_FILE=/tmp/${LOCAL_DB_NAME}.$(date +%F-%H%M).sql.gz
		# Avoid the "Using a password on the command line can be insecure warning: https://stackoverflow.com/a/34670902
		export MYSQL_PWD=${LOCAL_DB_PASS}
		printf "${LIGHTGREEN}Backing up local db to $BACKUP_FILE … ${NC}"
		$MYSQLDUMP -h "${LOCAL_DB_HOST}" -u "${LOCAL_DB_USER}" "${LOCAL_DB_NAME}" | gzip > $BACKUP_FILE
		if [[ $? -eq 0 ]] ; then
			printf "${GREEN}✔ ok${NC}\n"
		else
			printf "${RED}✘ failed! Exiting.${NC}\n"
			exit;
		fi

		# Fetch db
		printf "${ORANGE}Fetching db from remote … ${NC}"
		TMPFILE=$(mktemp)
		case $MODE in
			ssh)
				ssh ${HOSTNAME} "export MYSQL_PWD=${REMOTE_DB_PASS}; mysqldump -h ${REMOTE_DB_HOST} -u ${REMOTE_DB_USER} ${REMOTE_DB_NAME}" > ${TMPFILE}
				;;
			ftp)
				# If curl fails witha error code #35 try without `-k` option
				curl --post301 -Lsk \
					--data-urlencode "db_host=${REMOTE_DB_HOST}" \
					--data-urlencode "db_name=${REMOTE_DB_NAME}" \
					--data-urlencode "db_user=${REMOTE_DB_USER}" \
					--data-urlencode "db_pass=${REMOTE_DB_PASS}" \
					--output ${TMPFILE} \
					${HOSTNAME}/admin/sqldump.php
				;;
		esac
		if [[ $? -eq 0 ]] ; then
			printf "${GREEN}✔ ok${NC}\n"
		else
			printf "${RED}✘ failed! Exiting.${NC}\n"
			exit;
		fi

		if [ $DRY -eq 0 ] ; then
			printf "${ORANGE}Applying dump to local db … ${NC}"
			export MYSQL_PWD=${LOCAL_DB_PASS}
			cat $TMPFILE | $MYSQL -h "${LOCAL_DB_HOST}" -u "${LOCAL_DB_USER}" "${LOCAL_DB_NAME}"
			if [[ $? -eq 0 ]] ; then
				printf "${GREEN}✔ ok${NC}\n"
			else
				printf "${RED}✘ failed! Exiting.${NC}\n"
				exit;
			fi
		else
			printf "${ORANGE}Dry run! Not applying dump to local db … ${NC}\n"
		fi
	fi


	if [[ ${FETCH_FILES} == 1 ]] ; then

		for dir in ${FETCH_DIRS} ; do

			# 2019-05-21: Backup is handled by rsync option --backup now
			# Backup media files
			if [[ -d ${dir} && ${MODE} != "ssh" ]] ; then
				BACKUP_FILE=/tmp/${HOSTNAME}.${dir}.$(date +%F-%H%M).tar.gz
				printf "${LIGHTGREEN}Backing up local dir \`${dir}\` to ${BACKUP_FILE} … ${NC}"
				tar cfz ${BACKUP_FILE} ./${dir}/
				if [[ $? -eq 0 ]] ; then
					printf "${GREEN}✔ ok${NC}\n"
				else
					printf "${RED}✘ failed! Exiting.${NC}\n"
					exit;
				fi
			fi

			# Fetch media files
			printf "${ORANGE}Fetching dir \`${dir}\` from remote … ${NC}"
			case $MODE in
				ssh)
					rsync ${RSYNC_FETCH_OPTIONS} -e ssh ${HOSTNAME}:${REMOTE_DIR}/${dir}/ ./${dir}/
					;;
				ftp)
					echo "mirror ${FTP_FETCH_OPTIONS} ${REMOTE_DIR}/${dir}/ ./${dir}/" | lftp --user ${FTP_USERNAME} --env-password ${HOSTNAME} > /dev/null
					;;
			esac

			if [[ ${?} -eq 0 ]] ; then
				printf "${GREEN}✔ ok${NC}\n"
			else
				printf "${RED}✘ failed!${NC}\n"
			fi

			chmod -R a+rX ${dir}
		done
	fi
}



function deploy {
	for dir in ${DEPLOY_DIRS} ; do
		printf "Deploying \`${dir}\` … "
		case $MODE in
			ssh)
				rsync ${RSYNC_DEPLOY_OPTIONS} -e ssh ${dir}/ --exclude-from=".deployignore" ${HOSTNAME}:${REMOTE_DIR}/${dir}/ 
				;;
			ftp)
				echo "mirror --reverse ${FTP_DEPLOY_OPTIONS} ./${dir}/ ${REMOTE_DIR}/${dir}/" | lftp --user ${FTP_USERNAME} --env-password ${HOSTNAME} > /dev/null
				;;
		esac

		if [[ ${?} -eq 0 ]] ; then
			printf "${GREEN}✔ ok${NC}\n"
		else
			printf "${RED}✘ failed!${NC}\n"
		fi
	done
}



# Parse options
while getopts ":nhy" opt ; do
	case $opt in
		n)
			DRY=1
			RSYNC_FETCH_OPTIONS="${RSYNC_FETCH_OPTIONS}n"
			RSYNC_DEPLOY_OPTIONS="${RSYNC_DEPLOY_OPTIONS} --dry-run"
			FTP_FETCH_OPTIONS="${FTP_FETCH_OPTIONS} --dry-run"
			FTP_DEPLOY_REVERSE_OPTIONS="${FTP_DEPLOY_OPTIONS} --dry-run"
			;;
		y)
			INTERACTIVE=0
			;;
		h)
			usage
			exit 0
			;;
		\?)
			echo "Invalid option: -$OPTARG" >&2
			exit
			;;
	esac
done
shift $((OPTIND-1))

action=$1
case $action in
	fetch)
		fetch
		;;
	deploy)
		deploy
		;;
	*)
		printf "Unknown command: $action"
		usage
		exit 1
		;;
esac
