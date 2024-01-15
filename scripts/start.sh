#!/bin/bash

# Disable Strict Host checking for non interactive git clones

#Setup printer
/usr/sbin/cupsd -F &

mkdir -p -m 0700 /root/.ssh
echo -e "Host *\n\tStrictHostKeyChecking no\n" >> /root/.ssh/config

# Setup git variables
if [ ! -z "$GIT_EMAIL" ]; then
 git config --global user.email "$GIT_EMAIL"
fi
if [ ! -z "$GIT_NAME" ]; then
 git config --global user.name "$GIT_NAME"
 git config --global push.default simple
fi

# Install Extras
if [ ! -z "$DEBS" ]; then
 apt-get update
 apt-get install -y $DEBS
fi

# Pull down code form git for our site!
if [ ! -z "$GIT_REPO" ]; then
  rm /usr/share/nginx/html/*
  if [ ! -z "$GIT_BRANCH" ]; then
    git clone -b $GIT_BRANCH $GIT_REPO /usr/share/nginx/html/
  else
    git clone $GIT_REPO /usr/share/nginx/html/
  fi
  chown -Rf nginx.nginx /usr/share/nginx/*
fi

# Display PHP error's or not
if [[ "$ERRORS" != "1" ]] ; then
  sed -i -e "s/error_reporting =.*=/error_reporting = E_ALL/g" /etc/php/7.2/fpm/php.ini
  sed -i -e "s/display_errors =.*/display_errors = Off/g" /etc/php/7.2/fpm/php.ini
fi

# Tweak nginx to match the workers to cpu's

procs=$(cat /proc/cpuinfo |grep processor | wc -l)
sed -i -e "s/worker_processes 5/worker_processes $procs/" /etc/nginx/nginx.conf
sed -i -e "s/session.save_handler = files/session.save_handler = $SAVE_HANDLER/g" /etc/php/7.2/fpm/php.ini 
sed -i -e "s[redis_server["$REDIS_SERVER"[g" /etc/php/7.2/fpm/php.ini 
# Very dirty hack to replace variables in code with ENVIRONMENT values
if [[ "$TEMPLATE_NGINX_HTML" == "1" ]] ; then
  for i in $(env)
  do
    variable=$(echo "$i" | cut -d'=' -f1)
    value=$(echo "$i" | cut -d'=' -f2)
    if [[ "$variable" != '%s' ]] ; then
      replace='\$\$_'${variable}'_\$\$'
      find /usr/share/nginx/html -type f -exec sed -i -e 's/'${replace}'/'${value}'/g' {} \;
    fi
  done
fi

# Again set the right permissions (needed when mounting from a volume)
chown -Rf www-data.www-data /usr/share/nginx/html/

/usr/sbin/lpadmin -p receipt2 -E -v  lpd://192.168.1.110:515/PASSTHRU -m tm-m30-rastertotmt.ppd  -L "Shopfloor" -o pagesize="RP80x2000"
/usr/sbin/lpadmin -p receipt -E -v smb://user:user@192.168.1.2/receipt2 -m tm-ba-thermal-rastertotmt.ppd -L "Shopfloor" -o auth-info-required=negotiate
/usr/sbin/lpadmin -p barcode -E -v lpd://192.168.1.180:515/barcode -m drv:///sample.drv/zebra.ppd -L "Backoffice"
/usr/sbin/lpadmin -p main -E -v smb://cocorose:user@192.168.1.180/Canon2 -L "Backoffice Canon" -o auth-info-required=negotiate
/usr/bin/lpoptions -d receipt2 -o PageSize=RP80x2000
# Start supervisord and services
if [ "$CRON" == "ENABLED" ]
then
	echo "Starting CRON"
	cat /root/cron | crontab -
else
        echo "Starting CHECKER"
        cat /root/cron2 | crontab -
fi
/usr/bin/supervisord -n -c /etc/supervisord.conf