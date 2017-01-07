#!/bin/sh
mysqldump -u `cat wp-config.php | grep DB_USER | cut -d \' -f 4` -p`cat wp-config.php | grep DB_PASSWORD | cut -d \' -f 4` --add-drop-table --no-data  `cat wp-config.php | grep DB_NAME | cut -d \' -f 4`|grep ^DROP |  mysql -u `cat wp-config.php | grep DB_USER | cut -d \' -f 4` -p`cat wp-config.php | grep DB_PASSWORD | cut -d \' -f 4` `cat wp-config.php | grep DB_NAME | cut -d \' -f 4`

