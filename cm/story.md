# Deployment Story

## subdomain mgts - [orchestration]

- create subdomain entries in /etc/apache2/sites-available
- app.bst.com
- docs.bst.com
- bst.com

## copying source files - [deployment]

- copy /home/folarin/code/bst/app to /var/www/bst
- copy /home/folarin/code/bst/home to /var/www/bst
- copy /home/folarin/code/bst/docs to /var/www/bst

## symlink subdomain public directories to /var/www/html - [orchestration]

- sudo ln -s /var/www/bst/app/public /var/www/app.bst
- sudo ln -s /var/www/bst/docs /var/www/docs.bst
- sudo ln -s /var/www/bst/home /var/www/home.bst

## symlink assets folder

- sudo ln -s /var/www/bst/app/storage/public/assets /var/www/app.bst/public/
- sudo ln -s /var/www/bst/app/storage/public/uploads /var/www/bst/public/

## make storage folders writable - [orchestration]

- sudo chmod -R a+rw /var/www/bst/app/storage

## make sites-available entries for symlinks - [orchestration]

- edit /etc/apache2/sites-available/blockstale.com.conf
  - replace DocumentRoot to /var/www/home.bst
- cp bst.com to app.bst.com
  - replace DocumentRoot to /var/www/app.bst
- cp bst.com to docs.bst.com
  - replace DocumentRoot to /var/www/docs.bst

## make dns entries for new subdomains - [hands on]

- make entries for app.bst.com
- make entries for docs.bst.com

## blockchain service - [orchestration]

- copy /etc/systemd/system/blockchain.service /etc/systemd/system/blockchain.service [deployment]
- sudo systemctl enable blockchain.service
- sudo systemctl start blockchain.service

## cron callback scripts - [orchestration]

- make cron entry for btx-monitor.php
- make cron entry for zombie-btx-monitor.php

## DB Migration - [hands on]

- migrate blockstale_db to prod

## subsomains conf for prod - [hands on]

- delete blockstale.com.* confs
- replace bst.localhost with blockstale.com in confs

## change /etc/php/7.4[/apache2]/php.ini

- upload_max_filesize = 20M
- post_max_size = 21M
- max_file_uploads = 25
