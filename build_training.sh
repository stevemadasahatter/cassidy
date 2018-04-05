#!/bin/bash

cp ./conf/config_tr.php ./conf/config.php
cp ./conf/config_tr_b.php ./conf/config_b.php
cp ./code/cron_tr ./code/cron
cp ./src/favicon-t.ico ./src/favicon.ico

cp ./scripts/start_tr.sh ./scripts/start.sh

docker build -t localhost:5000/cassidy:training .
docker push localhost:5000/cassidy:training
