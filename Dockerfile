##############################################################################
# Dockerfile to run iPolex lexical data warehouse
# Based on php
#############################################################################
#
# Build part
#

FROM php:apache

LABEL maintainer="Mathieu.Mangeot@imag.fr"

ARG DICTIONNAIRES_SITE="/var/www/html/Dicos"
ARG DICTIONNAIRES_SITE_DAV="/var/www/html/DAV"
ARG DICTIONNAIRES_DAV="/DAV/Dicos"
ARG DICTIONNAIRES_WEB="/Dicos"
ARG DICTIONNAIRES_SITE_PUBLIC="/var/www/html/DicosPublic"
ARG DICTIONNAIRES_WEB_PUBLIC="/DicosPublic"
ARG ADMIN_USER="ipolex"
ARG ADMIN_PASSWORD="19013x"
ARG DEFAULT_TEST_USER=$ADMIN_USER


ENV DICTIONNAIRES_SITE=$DICTIONNAIRES_SITE
ENV DICTIONNAIRES_SITE_DAV=$DICTIONNAIRES_SITE_DAV
ENV DICTIONNAIRES_DAV=$DICTIONNAIRES_DAV
ENV DICTIONNAIRES_WEB=$DICTIONNAIRES_WEB
ENV DICTIONNAIRES_SITE_PUBLIC=$DICTIONNAIRES_SITE_PUBLIC
ENV DICTIONNAIRES_WEB_PUBLIC=$DICTIONNAIRES_WEB_PUBLIC
ENV ADMIN_USER=$ADMIN_USER
ENV ADMIN_PASSWORD=$ADMIN_PASSWORD
ENV DEFAULT_TEST_USER=$DEFAULT_TEST_USER

WORKDIR $DICTIONNAIRES_SITE
WORKDIR $DICTIONNAIRES_SITE_DAV
WORKDIR $DICTIONNAIRES_SITE_PUBLIC

RUN ln -s $DICTIONNAIRES_SITE $DICTIONNAIRES_SITE_DAV

RUN chown -R www-data:www-data $DICTIONNAIRES_SITE $DICTIONNAIRES_SITE_DAV $DICTIONNAIRES_SITE_PUBLIC

RUN mkdir -p /usr/share/man/man1 \
   && apt-get update && apt-get install -y libexpat1-dev \
      locales \ 
	  tree

RUN echo 'fr_FR.UTF-8 UTF-8' >> /etc/locale.gen \
   && echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen \
   && locale-gen

RUN sed -i "s#</VirtualHost>#<Directory "/var/www/html">\n \
   Options +Indexes +FollowSymLinks +MultiViews\n \
</Directory>\n \
<Directory \"$DICTIONNAIRES_SITE_DAV\">\n \
  DirectoryIndex none.none\n \
  DAV On\n \
  RemoveHandler .php\n \
  ForceType text/plain\n \
  php_flag engine off\n \
  AuthType Basic\n \
  AuthName \"iPolex WebDav Authentication\"\n \
  AuthBasicProvider file\n \
  AuthUserFile /etc/apache2/webdav.htpasswd\n \
  Require valid-user\n \
 </Directory>\n \
\n \
 </VirtualHost>#" /etc/apache2/sites-enabled/000-default.conf

RUN htpasswd -cb /etc/apache2/webdav.htpasswd $ADMIN_USER $ADMIN_PASSWORD

RUN /usr/sbin/a2enmod dav dav_fs dav_lock

# install MElt tagger https://gforge.inria.fr/frs/download.php/file/36209/melt-2.0b12.tar.gz
# RUN wget https://gforge.inria.fr/frs/download.php/file/36209/melt-2.0b12.tar.gz && \\
#	tar zxvf melt-2.0b12.tar.gz
	
# WORKDIR melt-2.0b12

RUN	aclocal && autoconf && automake -a && ./configure && make && make install

# install mecab
RUN git clone https://github.com/taku910/mecab.git
	
WORKDIR mecab/mecab

RUN ./configure && make && make install && ldconfig

WORKDIR mecab/mecab-ipadic

RUN ./configure && make && make install


# install uplug
RUN git clone https://bitbucket.org/tiedemann/uplug.git

WORKDIR uplug

RUN cpan install inc::Module::Install && make all && make install && make test

#install ipolex tools
RUN cpan install XML::Parser

# install CWB
# https://sourceforge.net/projects/cwb/files/cwb/cwb-3.0.0/cwb-3.0.0-linux-x86_64.tar.gz/download
# https://sourceforge.net/projects/cwb/files/cwb/cwb-3.0.0/cwb-3.0.0-linux-i386.tar.gz/download
RUN wget https://downloads.sourceforge.net/project/cwb/cwb/cwb-3.0.0/cwb-3.0.0-linux-i386.tar.gz && tar zxvf cwb-3.0.0-linux-i386.tar.gz

WORKDIR cwb-3.0.0-linux-i386

RUN ./install-cwb.sh


RUN docker-php-ext-install gettext

WORKDIR /var/www/html

COPY . .

RUN cp init.php.sample init.php

RUN sed -i "s#\%DICTIONNAIRES_SITE\%#$DICTIONNAIRES_SITE#g" init.php \ 
   && sed -i "s#\%DICTIONNAIRES_DAV\%#$DICTIONNAIRES_DAV#g" init.php \
   && sed -i "s#\%DICTIONNAIRES_WEB\%#$DICTIONNAIRES_WEB#g" init.php \
   && sed -i "s#\%DICTIONNAIRES_SITE_PUBLIC\%#$DICTIONNAIRES_SITE_PUBLIC#g" init.php \
   && sed -i "s#\%DICTIONNAIRES_WEB_PUBLIC\%#$DICTIONNAIRES_WEB_PUBLIC#g" init.php \
   && sed -i "s#\%DEFAULT_TEST_USER\%#$DEFAULT_TEST_USER#g" init.php
