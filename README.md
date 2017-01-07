# wordpress search replace

## cleardb.sh
Just wipe all the data from wordpress database. Caution: all tables disregarding prefixes.

## export.sh
Dumps wordpress tables to .dump.sql

## import.sh
Stores data from .dump.sql to wordpress database

##replace.php
Console scripts for sql search replace in the database with serialized/json data recalculated recursevly
Often needed during migrations to change domain name from old to new.

Most similar tools just do usual replace and recalculate serialized data.
This script checks column value if it is string, serialized value or json. If needed converts it to array and walks recursevely
each time checking again and again what kind of data is stored deeper. 
Very often plugin developers json encode some data and then serialize it or even store json data in the values of some multidimensial arrays.


Developed by <a href="http://cweb24.com">cweb24.com</a>

