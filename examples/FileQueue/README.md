# Slime MultiJob File Queue Example

1. /your_path_of_php_bin /your_path_of_project/examples/FileQueue/MyDaemon.php 10

2. /your_path_of_php_bin /your_path_of_project/examples/FileQueue/MyJob.php push 10

## @TODO?
I need some help!
If job number set bigger, zombie process may be product ?

### You can do a test :
1. Terminal 1: watch 'ps -ef|grep php|grep -v fpm|grep -v grep|grep -v watch'

2. Terminal 2:
   /your_path_of_php_bin /your_path_of_project/examples/FileQueue/MyDaemon.php 30
   /your_path_of_php_bin /your_path_of_project/examples/FileQueue/MyJob.php push 30