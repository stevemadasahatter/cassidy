#!/bin/bash

cp ./conf/config_cr.php ./conf/config.php
cp ./conf/config_cr_b.php ./conf/config_b.php
cp ./code/cron_cr ./code/cron
cp ./src/favicon-cr.ico ./src/favicon.ico

cp ./scripts/start_cr.sh ./scripts/start.sh

docker build -t localhost:5000/cassidy:cocorose .
docker push localhost:5000/cassidy:cocorose
