apt-get update -y

apt-get install -y zip unzip
apt-get install -y apache2
service apache2 stop

sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password temp'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password temp'
apt-get install -y mysql-server

apt-get install -y php
apt-get install -y libapache2-mod-php
apt-get install -y php-curl
apt-get install -y php-mysql
apt-get install -y php-pdo
apt-get install -y phpunit
apt-get install -y memcached php-memcached

apt-get install -y composer
apt-get install -y nodejs


a2enmod rewrite

rm -rf /etc/apache2/sites-enabled
rm -rf /etc/apache2/apache2.conf
ln -fs /vagrant/config/apache/sites-enabled /etc/apache2/sites-enabled
ln -fs /vagrant/config/apache/apache2.conf /etc/apache2/apache2.conf

rm -rf /var/www
ln -fs /vagrant/www /var/www

mysql -u root --password='temp' -e "SET PASSWORD FOR 'root'@'localhost' = PASSWORD('')"
mysql -u root -e 'CREATE DATABASE warcraft_mount_duelist'

service apache2 start