#-----------------------------------------------------------------------------
#                      prepends - Service org-ws-core
#-----------------------------------------------------------------------------
#
#   File    : docker/core/Dockerfile
#
#   Author  : Nicolas DUPRE
#   Version : 0.7.1
#   Started : 10.02.2018
#   Release : 22.05.2018
#   Status  : DEV-WIP
#
#   Description :
#     Instructions de construction de l'image Docker pour le developpement.
#
FROM debian:jessie

MAINTAINER Nicolas DUPRE <neo-blaster@hotmail.fr>

SHELL ["/bin/bash", "-c"]

ENV DEFAULT_LOCALE=en_US \
    NGINX_VERSION=stable
ENV MYSQL_ROOT_PASSWORD="mysqladmin"
ENV MYSQL_ORG_PASSWORD="OrG"
ENV TOOLS_PACKAGES="nano debconf-utils wget git"
ENV DB_PACKAGES="mysql-server mysql-client"
ENV WEB_PACKAGES="php5-fpm php5-cli php5-json php5-mysql php5-memcache php5-memcached php5-curl php5-gd php5-imap php5-mcrypt php5-dev php5-xdebug nginx-full"
ENV DEV_PACKAGES=""



#-----------------------------------------------------------------------------
#                           Installation du système
#-----------------------------------------------------------------------------
RUN export LANG=${DEFAULT_LOCALE}.UTF-8

RUN apt-get update && apt-get install -y --no-install-recommends apt-utils

# Installation des outils Linux.
RUN apt-get install -y $TOOLS_PACKAGES

# Installation des packages web requis pour Origins-Esport WebSite.
#───┐ Définition du mot de passe MySQL.
RUN debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD"
RUN debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD"
#───┐ Installation des packages
RUN apt-get install -y $DB_PACKAGES $WEB_PACKAGES

# Installation des ressources de développements.
RUN apt-get install -y $DEV_PACKAGES



#-----------------------------------------------------------------------------
#                           Configuration du système
#-----------------------------------------------------------------------------
# Configuration de MySQL
#───┐ Configurer l'écoute pour toutes les adresses. (Peut etre supprimée par comment)
RUN sed -i -re "s/bind-address(\s+=\s+)([0-9]+\.?)+/bind-address\1*/" /etc/mysql/my.cnf



#-----------------------------------------------------------------------------
#                            Livraison des fichiers
#-----------------------------------------------------------------------------
# Fichier d'execution finale
COPY ./config/entrypoint.sh /

# Fichier de configuration NGINX à utiliser
COPY ./config/nginx/default /etc/nginx/sites-enabled/

# Fichier de configuration PHP à utiliser
COPY ./config/php/php5/fpm/php-custom.ini /etc/php5/fpm/php.ini

# Fichier de développement
COPY ./config/www/index.php /var/www/index.php



#-----------------------------------------------------------------------------
#                              Mappage des volumes
#-----------------------------------------------------------------------------



#-----------------------------------------------------------------------------
#                                 Finalisation
#-----------------------------------------------------------------------------
# Ouvertures des ports.
EXPOSE 80
EXPOSE 443
EXPOSE 3306

# Execution du container en tant que commande exécutable.
ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]

# Dossier de travail lorsqu'on se connecterau container.
WORKDIR /var/www/prepends
