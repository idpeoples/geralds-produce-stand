create database bananas;
create user bananas_user@localhost identified by 'example';
grant all privileges on bananas.* to bananas_user@localhost identified by 'example';
flush privileges;
