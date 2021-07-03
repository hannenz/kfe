#!/bin/bash
# -----------------------------------------------------------------------------
# @author Johannes Braun <j.braun@agentur-halma.de>
# @version 2021-03-16
#
# Backup script to be run as cronjob for periodically backing up the
# production environment
# Makes a daily backup every day and keeps one each week for the latest month and one for each month (forever)
#
# Set MYSQL_PWD as environment variable,  so we can safely have this script in Git
# e.g. in crontab, can be used like this:
# ```
# MYSQL_PWD='secret'
# 0 3 * * * /path/to/mkbackup.cron.sh
# ```
#
# or run like this:
# ```
# MYSQL_PWD=secret bash -x mkbackup.cron.sh
# ```
# -----------------------------------------------------------------------------
# Settings
# -----------------------------------------------------------------------------

# The source dir to be backup'ed (Full path)
src="./"

# The destination path for backups (Full path)
dest="./backups/cron"

# Excludes: One exclude per line
excludes="
settings_db.inc
settings_mail.inc
./tmp
"

# Database credentials
dbhost=kinderflohmarkt_erbach_de.mysql
dbname=kinderflohmarkt_erbach_de
dbuser=kinderflohmarkt_erbach_de


# ------------------------------------------------------------
# Do not change anything below this line!
# ------------------------------------------------------------
exclude=""
for item in ${excludes} ; do
	exclude="${exclude} --exclude=\"${item}\" "
done

# The current weekday (1=Monday)
weekday=$(date +%u)
dayofmoth=$(date +%d)
fulldate=$(date +%Y-%m-%d)
fulldatetime=$(date +%Y-%m-%d-%H%M%S)

daily_backup_filename="backup.daily.${weekday}.tar.gz"
weekly_backup_basename="backup.weekly"
monthly_backup_basename="backup.monthly"
weekly_backup_filename="${weekly_backup_basename}.${fulldate}.tar.gz"
monthly_backup_filename="${monthly_backup_basename}.${fulldate}.tar.gz"

tmp="$(dirname $(mktemp -u))/"
db_dumpfile="${tmp}${dbname}.${fulldatetime}.sql"

# Create destination directory if it does not exist yet
[[ -d "${dest}" ]] || mkdir -p "${dest}"

# Exit if source directory does not exist
[[ -d "${src}" ]] || exit -1;


# Every monday, archive last monday's backup
if [[ $weekday == "1" ]] ; then
	if [[ -e "${dest}/${daily_backup_filename}" ]] ; then
		mv "${dest}/${daily_backup_filename}" "${dest}/${weekly_backup_filename}"
	fi
fi

# Every 1st of month, backup the latest weekly backup, rm all other
if [[ $dayofmonth == "01" ]] ; then
	# Find the newest weekly backup
	latest_weekly=$(ls -t "${dest}/${weekly_backup_basename}.*" | head -1)
	if [[ $? -eq 0 ]] ; then
		mv "${dest}/${latest_weekly}" "${dest}/${monthly_backup_filename}" && rm -f "${dest}/${weekly_backup_basename}.*"
	fi
fi


# Make daily backup
# 1. Database dump
mysqldump --no-tablespaces -h "${dbhost}" -u "${dbuser}" "${dbname}" > "${db_dumpfile}"

# 2. Files
tar --create --gzip ${exclude} --file "${dest}/${daily_backup_filename}" "${src}" "${db_dumpfile}"

chmod 600 "${dest}/${daily_backup_filename}"

# 5. Clean-up
rm -f "${db_dumpfile}"

exit 0;
