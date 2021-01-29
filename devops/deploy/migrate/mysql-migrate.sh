# Backup
docker exec CONTAINER /usr/bin/mysqldump -u root --password=root DATABASE > backup.sql

# Restore
cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u root --password=root DATABASE

#2
Backup
docker exec CONTAINER /usr/bin/mysqldump -u root --password=root DATABASE > backup.sql
Restore
docker exec -i CONTAINER /usr/bin/mysql -u root --password=root DATABASE < backup.sql

#3
# get IP 
docker inspect CONTAINER | grep IPAddress 
# for restore, db must be exist
mysql -uroot -ppass -h IPAddress DB_NAME -P 3306 < db_backup.sql
# for backup 
mysqldump -uroot -ppass IPAddress DB_NAME -P 3306 > db_backup.sql
