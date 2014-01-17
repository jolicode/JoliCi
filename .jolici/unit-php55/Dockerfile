FROM ubuntu
MAINTAINER Joel Wurtz <jwurtz@jolicode.com>

# Adding php 5.5 repo
RUN echo 'deb http://fr.archive.ubuntu.com/ubuntu precise main universe' > /etc/apt/sources.list
RUN echo 'deb http://fr.archive.ubuntu.com/ubuntu precise-security main universe' >> /etc/apt/sources.list
RUN echo 'deb http://fr.archive.ubuntu.com/ubuntu precise-updates main universe' >> /etc/apt/sources.list
RUN apt-get update 
RUN apt-get install -y python-software-properties
RUN add-apt-repository ppa:ondrej/php5 
RUN apt-get update

# Install deps
RUN apt-get install -y php5-cli git

ADD . /jolici

RUN cd /jolici && php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));" && php composer.phar install --dev --prefer-source

CMD cd /jolici && php vendor/bin/phpunit