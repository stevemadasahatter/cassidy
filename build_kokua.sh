#!/bin/bash

cp ./conf/config_k.php ./conf/config.php
cp ./conf/config_k_b.php ./conf/config_b.php
cp ./code/cron_k ./code/cron
cp ./conf/k-logo.png ./code/pos/images/1-logo.png
cp ./conf/k-logo.png ./code/backend/images/1-logo.png


cp ./scripts/start_k.sh ./scripts/start.sh

docker build -t localhost:5000/cassidy:kokua .
docker push localhost:5000/cassidy:kokua
