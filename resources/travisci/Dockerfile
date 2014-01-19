FROM ubuntu
MAINTAINER Joel Wurtz <jwurtz@jolicode.com>

# Default ENV for TravisCi
ENV CI     true
ENV TRAVIS false
ENV DEBIAN_FRONTEND noninteractive
ENV USER root
ENV HOME /root
ENV LANG C.UTF-8
ENV LC_ALL C.UTF-8
ENV RAILS_ENV test
ENV RACK_ENV test
ENV MERB_ENV test
ENV JRUBY_OPTS "--server -Dcext.enabled=false -Xcompile.invokedynamic=false"
ENV TRAVIS_BRANCH local
ENV TRAVIS_BUILD_DIR $HOME/project
ENV TRAVIS_BUILD_ID 0
ENV TRAVIS_BUILD_NUMBER 0
ENV TRAVIS_COMMIT ""
ENV TRAVIS_COMMIT_RANGE ""
ENV TRAVIS_JOB_ID 0
ENV TRAVIS_JOB_NUMBER 0
ENV TRAVIS_PULL_REQUEST false
ENV TRAVIS_SECURE_ENV_VARS false
ENV TRAVIS_REPO_SLUG ""

# Add apt repository needed
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise main universe' > /etc/apt/sources.list
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise-security main universe' >> /etc/apt/sources.list
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise-updates main universe' >> /etc/apt/sources.list
RUN echo 'deb http://archive.ubuntu.com/ubuntu precise-backports main restricted universe multiverse' >> /etc/apt/sources.list
RUN apt-get update && apt-get install -y python-software-properties && add-apt-repository ppa:pdoes/ppa && apt-get update

# Common for all
RUN apt-get install -y git sudo curl wget
RUN mkdir -p $HOME