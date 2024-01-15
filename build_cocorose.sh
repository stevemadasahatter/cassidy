#!/bin/bash

cp ./conf/config_cr.php ./conf/config.php
cp ./conf/config_cr_b.php ./conf/config_b.php
cp ./code/pos/report/emailReceipt-cr.html ./code/pos/report/emailReceipt.html
cp ./code/cron_cr /root/cron

cp ./scripts/start_cr.sh ./scripts/start.sh

docker build -f Dockerfile_new -t eu.gcr.io/delta-repeater-254320/cassidy:cocoroseint .
docker push eu.gcr.io/delta-repeater-254320/cassidy:cocoroseint
