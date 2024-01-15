FROM ubuntu:18.04
MAINTAINER Ric Harvey <ric@ngineered.co.uk>

# Surpress Upstart errors/warning
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -sf /bin/true /sbin/initctl

# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive
# Update base image
# Add sources for latest nginx
# Install software requirements
RUN apt-get update && \
apt-get install -y software-properties-common && \
nginx=stable && \
add-apt-repository ppa:nginx/$nginx && \
apt-get update && \
apt-get upgrade -y && \
BUILD_PACKAGES="composer2 curl supervisor nginx php-pear php7.2-zip php7.2-mbstring php7.2-fpm git php7.2-mysql php7.2-curl php7.2-gd php7.2-intl php7.2-sqlite php7.2-soap php7.2-tidy php7.2-xmlrpc php7.2-xsl php7.2-pgsql php7.2-ldap php7.2-redis pwgen sshpass cups samba samba-common smbclient vim" && \
apt-get -y install $BUILD_PACKAGES && \
apt-get remove --purge -y software-properties-common && \
apt-get autoremove -y && \
apt-get clean && \
apt-get autoclean 
#pear install Mail && \
#pear install Net_SMTP && \
RUN composer require pear/mail
RUN composer require pear/net_smtp
RUN echo -n > /var/lib/apt/extended_states && \
rm -rf /var/lib/apt/lists/* && \
rm -rf /usr/share/man/?? && \
rm -rf /usr/share/man/??_*

# tweak nginx config
RUN sed -i -e"s/worker_processes auto/worker_processes 80/" /etc/nginx/nginx.conf && \
sed -i -e"s/keepalive_timeout\s*65/keepalive_timeout 1/" /etc/nginx/nginx.conf && \
sed -i -e"s/worker_connections 768/worker_connections 4096/" /etc/nginx/nginx.conf && \
sed -i -e"s/keepalive_timeout 1/keepalive_timeout 1;\n\tclient_max_body_size 100m/" /etc/nginx/nginx.conf && \
echo "daemon off;" >> /etc/nginx/nginx.conf

# tweak php-fpm config
RUN sed -i -e "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s/upload_max_filesize\s*=\s*2M/upload_max_filesize = 100M/g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s/error_reporting/;error_reporting/g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s/; Common Values:/error_reporting=E_ERROR/g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s[session.cookie_httponly =[session.save_path=\"redis_server\"[g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s/display_error=On/display_errors=Off/g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s/post_max_size\s*=\s*8M/post_max_size = 100M/g" /etc/php/7.2/fpm/php.ini && \
sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php/7.2/fpm/php-fpm.conf && \
sed -i -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i -e "s/pm.max_children = 5/pm.max_children = 50/g" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i -e "s/pm.start_servers = 2/pm.start_servers = 30/g" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 10/g" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 45/g" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i -e "s/pm.max_requests = 500/pm.max_requests = 600/g" /etc/php/7.2/fpm/pool.d/www.conf && \
sed -i -e "/[global]/a client max protocol = SMB2" /etc/samba/smb.conf && \
sed -i -e "/[global]/a server max protocol = SMB2" /etc/samba/smb.conf

# fix ownership of sock file for php-fpm
RUN sed -i -e "s/;listen.mode = 0660/listen.mode = 0750/g" /etc/php/7.2/fpm/pool.d/www.conf && \
find /etc/php/7.2/cli/conf.d/ -name "*.ini" -exec sed -i -re 's/^(\s*)#(.*)/\1;\2/g' {} \;

# mycrypt conf
#RUN phpenmod mcrypt

# nginx site conf
RUN rm -Rf /etc/nginx/conf.d/* 
USER root
RUN rm -f /etc/nginx/sites-available/default && \
mkdir -p /etc/nginx/ssl/
RUN mkdir -p /run/php
ADD conf/nginx-site.conf /etc/nginx/sites-available/default
#RUN ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default
#RUN ln -s /etc/nginx/sites-available/default.conf /etc/nginx/sites-enabled/default.conf

# Add git commands to allow container updating
ADD scripts/pull /usr/bin/pull
ADD scripts/push /usr/bin/push
RUN chmod 755 /usr/bin/pull && chmod 755 /usr/bin/push

# Supervisor Config
ADD conf/supervisord.conf /etc/supervisord.conf

# Start Supervisord
ADD scripts/start.sh /start.sh
ADD scripts/tmx-cups-2.0.2.0.tar.gz /root/tmx-cups-2.0.2.0.tar.gz
ADD scripts/tmx-cups-2.0.2.101.tar.gz /root/tmx-cups-2.0.2.101.tar.gz
RUN chmod 755 /start.sh

# Setup Volume
VOLUME ["/var/www"]

# add test PHP file
ADD src/index.php /var/www/index.php
ADD src/favicon.ico /var/www/favicon.ico
ADD code/pos	/var/www/pos
ADD code/backend /var/www/backend
ADD conf/config.php /var/www/pos/config.php
ADD conf/config_b.php /var/www/backend/config.php
ADD conf/config_w.php /var/www/backend/website/config.php
ADD code/updatedns.php /var/www/updatedns.php
ADD code/cron /root/cron
ADD conf/cupsd.conf  /etc/cups/cupsd.conf
ADD php /usr/share/php
RUN chmod 664 /root/cron
RUN touch /var/log/cron.log
RUN chown -Rf www-data.www-data /var/www/
RUN echo "8\n" | /root/tmx-cups-2.0.2.0.tar.gz/tmx-cups/install.sh
RUN echo "8\n" | /root/tmx-cups-2.0.2.101.tar.gz/tmx-cups/install.sh
RUN cp /usr/share/ppd/Epson/tm-ba-thermal-rastertotmt.ppd.gz /usr/share/cups/model
RUN cp /usr/share/ppd/Epson/tm-m30-rastertotmt.ppd.gz /usr/share/cups/model
RUN gunzip /usr/share/cups/model/tm-ba-thermal-rastertotmt.ppd.gz
RUN gunzip /usr/share/cups/model/tm-m30-rastertotmt.ppd.gz
RUN chmod -R 777 /var/www/pos/tmp
RUN chmod -R 777 /var/www/backend/tmp
RUN chmod -R 777 /usr/share/php
RUN chmod -R 777 /var/www/backend/report/automated.sh
RUN touch /var/www/backend/website/batch_output_m2.txt
RUN chmod -R 777 /var/www/backend/website/batch_output_m2.txt

#Checkout the till

# Expose Ports
EXPOSE 443
EXPOSE 80
EXPOSE 631

CMD ["/bin/bash", "/start.sh"]
