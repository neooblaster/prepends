# Docker ``prepends`` - Version 1.0.0

Ce container est construit à partir de ``Debian Jessie``.

Pour y accéder, tapez la commande ``make prepends``.



## Sommaire

[](MakeSummary)



## Packages principaux inclus et Credentials configurés

Est listé ci-dessous les packages principaux qui sont inclus dans le
container ainsi que les comptes nécessaire.
Pour avoir la liste complète, se référer au fichier ``Dockerfile``.

**Liste des packages :**

* Les outils :
	* nano wget
	* debconf
	* git
* Les services :
	* mysql-server
	* mysql-client
	* NGINX
	* php5.6-fpm :
		* composer

**Liste des comptes :**

* Comptes SQL :
	* ``root`` : ``mysqladmin`` de scope `locahost`
	* ``OrG`` : ``OrG`` de scope `%`
* Base de données :
	* ``OrG``



## Utilisation


### Page Web

Pour accéder à la page web, il suffit d'aller à l'adresse suivante :
[http://localhost](http://localhost)



### Serveur MySQL

A l'aide de votre outil MySQL (e.g. MySQL Workbench), saisissez ``localhost``
sur le port ``3306``. Utilisez le compte `OrG` de mot de passe `OrG` et
sélectionnez la base de donnée `OrG`.



### Accéder au serveur

Pour accéder à la machine, tapez la commande ``make prepends``



### Accéder au logs du site web

D'abord il faut d'abord accéder au serveur via le chapitre précédent.
Ensuite tapez la commande :

* Pour accéder au logs d'accès : ``tail -f /var/log/nginx/org-ws.access.log``
* Pour accéder au logs d'erreur : ``tail -f /var/log/nginx/org-ws.error.log``



## Explication



## Changelog


### Version 1.0.0 (2018-03-14) :

- Fichier ``config/php/php5/fpm/php-custom.ini`` : Configuration de la dépendance `prepends`.






[!ADDED]:#
[!FIXED]:#
[!CHANGED]:#
[!REMOVED]:#
[!SECURITY]:#
[!DEPRECATED]:#
[!OTHER]:#
[!BUGFIX]:#
