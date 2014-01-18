FROM ubuntu
MAINTAINER Joel Wurtz <jwurtz@jolicode.com>

# Add apt repository needed
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise main universe' > /etc/apt/sources.list
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise-security main universe' >> /etc/apt/sources.list
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise-updates main universe' >> /etc/apt/sources.list
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise-backports main restricted universe multiverse' >> /etc/apt/sources.list
RUN apt-get update && apt-get install -y python-software-properties && add-apt-repository ppa:pdoes/ppa && apt-get update

# Common for all
RUN apt-get install -y subversion mercurial build-essential openssl rsync git sudo curl wget golang-go