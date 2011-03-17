Partuza Boundle
---------------

This Package has a copy of partuza and shindig2, partuza was modified to
work with shindig2.

Instalation
-----------

1) Add into your host file this

   127.0.0.1   partuza shindig

2) Edit the Paths in apache-vhost.conf, put a valid path to this folder

3) Put the file apache-vhost.conf into your apache configuration

4) Create the Database and load the Schema

  $ mysqladmin create partuza -u root -p
  $ mysql -u root -p partuza < /path/to/partuza2/partuza/partuza.sql 

5) Set into partuza/html/config.php the user credentials for the database

  'db_host' => 'localhost',
  'db_user' => 'root',
  'db_passwd' => '***',
  'db_database' => 'partuza',

6) Enjoy

NOTE:

If you have problems don't forget clear the cache (rm -rf /tmp/shindig).


