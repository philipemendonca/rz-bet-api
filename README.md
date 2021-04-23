# RZ-Bet API
API para retornar mercados de jogos esportivos do site: [bet365.com](https://www.bet365.com/).

## Analistas:
[@philipemendonca](https://github.com/philipemendonca/)<br>
[@danilorcsilva](https://github.com/danilorcsilva/)

# Requisitos de versão
- Apache 2.4.38 (Debian) ou mais recente.
- PHP 7.3.27-1 ou mais recente.
- Composer 2.0.12 ou mais recente.
- MySQL 15.1 Distrib 10.3.27-MariaDB ou mais recente.
- Node.JS 10.24.0 ou mais recente.
- NPM 7.7.6 ou superior.

# Requisitos de sistema
- Debian GNU/Linux 10 (buster) ou mais recente.
- RAM: 2 Gb.
- Processador: 2 Ghz.
- SSD: 128 Gb.

# Instalação de dependencias diretas
```shell
sudo apt-get install git
sudo apt-get install curl
sudo apt-get install wget
```

# Baixar repositório do projeto
```shell
git clone git@github.com:philipemendonca/rz-bet-api.git /var/www/html
```

# instalação e configuração do web server
```shell
#
# Apache 2
#
sudo apt-get install apache2
# Remover listagem de diretórios do Apache
# Editar os arquivos abaixo: 000-default.conf, e dicionar a string : Options -Indexes
sudo nano /etc/apache2/sites-available/000-default.conf
sudo nano /etc/apache2/sites-enabled/000-default.conf
#
# PHP 7.*
#
sudo apt-get install php7*
sudo apt-get install libapache2-mod-php 
sudo apt-get install php-mbstring 
sudo apt-get install php-zip
sudo apt-get install php7*-gd
sudo apt-get install php-mysql
#
# MySQL
#
sudo apt-get install install mariadb-server
sudo mysql_secure_installation
#
# PhpMyAdmin 4.9.7 (OPCIONAL)
#
wget https://files.phpmyadmin.net/phpMyAdmin/4.9.7/phpMyAdmin-4.9.7-all-languages.tar.gz
tar xvf phpMyAdmin-4.9.7-all-languages.tar.gz
sudo mv phpMyAdmin-4.9.7-all-languages/ /usr/share/phpmyadmin
sudo mkdir -p /var/lib/phpmyadmin/tmp
sudo chown -R www-data:www-data /var/lib/phpmyadmin
sudo cp /usr/share/phpmyadmin/config.sample.inc.php /usr/share/phpmyadmin/config.inc.php
sudo nano /usr/share/phpmyadmin/config.inc.php
# Criar chave aqui: $cfg['blowfish_secret'] = '';
# Criar usuario do phpmyadmin: $cfg['Servers'][$i]['controluser'] = 'pma';
# Criar senha do phpmyadmin: $cfg['Servers'][$i]['controlpass'] = 'password';
# Descomentar tudo no bloco: /* Storage database and tables */
# Acrescentar no final do arquivo: $cfg['TempDir'] = '/var/lib/phpmyadmin/tmp';
sudo mariadb < /usr/share/phpmyadmin/sql/create_tables.sql
sudo mariadb
```
```mysql
/* Inserir o usuario do phpmyadmin (OPCIONAL) */
GRANT SELECT, INSERT, UPDATE, DELETE ON phpmyadmin.* TO 'pma'@'localhost' IDENTIFIED BY 'password';
/* Inserir o usuario para usar o mysql */
GRANT ALL PRIVILEGES ON *.* TO 'seu-usuario'@'localhost' IDENTIFIED BY 'sua-senha' WITH GRANT OPTION;
exit
```
```shell
# Copiar o arquivo: phpmyadmin.conf, que esta na raiz do projeto (OPCIONAL)
sudo cp phpmyadmin.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin.conf
sudo service apache2 restart
#
# Configurações do apache
#
sudo a2enmod rewrite
# Mover o arquivo: 000-default.conf, que esta na raiz do projeto
sudo mv 000-default.conf /etc/apache2/sites-enabled/000-default.conf
sudo service apache2 restart
# Habilitar todas as extenções do php
sudo nano /etc/php/7.*/apache2/php.ini
#
# Permissões ao projeto
#
sudo chmod 777 /var/www/html/rz-bet-api/ -R
#
# Baixar dependencias do projeto (Composer)
#
cd /var/www/html/rz-bet-api/
# Para desenvolvimento
composer install
composer update
# Para produção
composer install --no-dev --optimize-autoloader
composer update --no-dev --optimize-autoloader
#
# Baixar dependencias do projeto (NPM)
#
cd /var/www/html/rz-bet-api/
# Para desenvolvimento
npm install
# Para produção
npm i --production
```