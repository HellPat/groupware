#!/usr/bin/env bash
set -euxo pipefail

touch ${TMPDIR}/errmsg.sys
touch ${TMPDIR}/error.log

# initialize mysql when the directory does not exist
if [ ! -d ${MYSQL_DATADIR} ]; then
  mkdir -p ${MYSQL_DATADIR}
  mysqld \
    --basedir=${MYSQL_HOME}/ \
    --datadir=${MYSQL_DATADIR}/ \
    --initialize-insecure \
    --default-time-zone=SYSTEM \
    --log-error=${TMPDIR}/error.log \
    --lc_messages_dir=${TMPDIR} \
    --lc_messages=en_US \
    --console
fi