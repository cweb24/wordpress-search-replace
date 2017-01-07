#!/bin/sh
mysql -u `cat wp-config.php | grep DB_USER | cut -d \' -f 4` -p`cat wp-config.php | grep DB_PASSWORD | cut -d \' -f 4`  `cat wp-config.php | grep DB_NAME | cut -d \' -f 4` -h `cat wp-config.php | grep DB_HOST | cut -d \' -f 4` < .dump.sql
