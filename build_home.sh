#!/bin/bash

cp ./conf/config_h.php ./conf/config.php
cp ./conf/config_h_b.php ./conf/config_b.php
cp ./code/cron_h ./code/cron

cp ./scripts/start_h.sh ./scripts/start.sh

docker build -t localhost:5000/cassidy:home .
docker push localhost:5000/cassidy:home
