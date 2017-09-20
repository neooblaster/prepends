# PHP Prepended files

Dans **PHP**, il est possible d'automatiser l'intégration systématique de fichier php avant l'exécution et après l'exécution d'un script PHP. Ce sont respectivement les deux directives internes **auto_prepend_file** et **auto_append_file** qui permettent cette automatisation.

> Ces deux directives sont disponibles quelle que soit la version de PHP.

Pour utiliser ces fonctionnalités, il faut d'abord les configurer dans le fichier de configuration **PHP** ``php.ini``.

La particularité de ces deux fonctionnalités est qu'il faut spécifier, chaque fichier à inclure.

Pour simplifier l'utilisation de ces deux options, j'ai conçu un script qui parcourt les différents fichiers et dossiers qui se trouvent à son emplacement. Ainsi seul ce script doit être **prepended** ou **appended** dans le fichier de configuration `php.ini`.

Il a l'avantage également d'être configurable pour ajuster au mieux le comportement du script selon les différents sites virtuels qui pourraient être hébergés sur le serveur en évitant ainsi des erreurs de redéclaration de classes, de fonctions ou encore des fichiers de configuration pouvant provoquer des comportements non désirés.

Un exemple d'utilisation de l'`auto_prepend_file` est l'intégration de fonctions globales basiques propriétaires qui pourront être utilisées dans n'importe quel script PHP sans devoir à chaque fois faire l'inclusion manuellement.


## 1. Configuration du fichier ``php.ini``

> La configuration suivante est déjà effective dans la machine virtuelle **MCOSDE**.

Ci-dessous, la définition des deux directives issues du **manuel PHP** :

```
auto_prepend_file string
    Spécifie le nom d'un fichier qui sera automatiquement parcouru avant le fichier principal. 
    Ce fichier est inclus comme s'il l'avait été avec la fonction require, donc include_path est utilisé.
    
    La valeur spéciale none désactive l'ajout automatique.
```

```
auto_append_file string
    Spécifie le nom du fichier qui sera automatiquement parcouru après le fichier principal.
    Ce fichier est inclus comme s'il l'avait été avec la fonction require, donc include_path est utilisé.
    
    La valeur spéciale none désactive l'ajout automatique.
    
    Note: Si le script se termine par la fonction exit(), l'ajout automatique ne se fera pas.
```

Dans notre configuration, nous allons spécifier un fichier unique qui est ``auto_prepend_files.php``.

Nous allons donc éditer le fichier de configurtion ``php.ini`` à l'aide de l'éditeur ``nano`` :

```bash
sudo nano /etc/php5/fpm/php.ini
```

Cherchez la directive de configuration ``auto_prepend_file`` à l'aide de la combinaison de touches suivantes : `CTRL + W` puis tapez `auto_prepend_file`.

* Si la ligne commence par un **point-virgule** (`;`, supprimez-le (ligne commentée).
* Inscrivez le nom du script ``auto_prepend_files.php`` derrière le signe `=`.

Vous devriez avoir quelque chose comme ceci :

```bash
; Automatically add files before PHP document.
; http://php.net/auto-prepend-file
auto_prepend_file = auto_prepend_files.php
```

Nous n'avons pas spécifié l'emplacement complet vers ce fichier, car nous allons exploiter la fonctionnalité des ``include_path``. Lorsqu'on appelle un fichier, **PHP** cherche ce fichier dans une liste de dossier définis dans l'option `include_path`.

De nouveau dans l'éditeur nano, faites ``CTRL + W`` et tapez `include_path`.

* Si la ligne commence par un **point-virgule** (`;`), supprimez-le (ligne commentée).
* Pour séparer plusieurs valeur, utilisez les **deux-points** (`:`).
* Inscrivez l'emplacement du dossier dans lequel se trouve le script ``auto_prepend_files.php``, dans notre cas : `/var/www/Prepends`.

Vous devriez avoir quelque chose comme ceci :

```bash
; UNIX : "/path1:/path2
include_path = ".:/usr/share/php:/var/www/Prepends"
```

La configuration étant désormais terminée, il ne reste plus qu'à redémarrer le service **PHP** pour qu'elle soit effective :

```bash
sudo service php5-fpm restart
```


## 2. Configuration de ``auto_prepend_files.php`` :

Automatiser l'inclusion de nombreux fichiers présent dans des dossiers et sous-dossiers peut être nuisible lorsque plusieurs sites virtuels cohabitent sur le même serveur. Il fallait donc un système pour ajuster le comportement du script afin de lui préciser des règles d'exclusion d'intégration. Ce fichier de configuration est le fichier ``.ignore`` qui doit se trouver dans le même emplacement que le script `auto_prepend_files.php`.

Le principe de fonctionnement du fichier est très similaire aux fichiers de configurations de **NGINX**.

### 2.1. Les commentaires :

#### 2.1.1. Les commentaires en ligne

J'ai choisi d'intégrer les trois modèles de commentaire en ligne les plus courants :

* ``#`` **Dièse** : Utilisé dans les fichiers de configuration **NGINX**, **MySQL**, **Bash**, **C/C++** et bien d'autre.
* ``;`` **Point-virgule** : Utilisé dans les fichiers de configuration **PHP**.
* ``//`` **Double slash** : Utilisé dans les scripts **PHP**, **JavaScript**.

#### 2.1.2. Les commentaires multi-ligne

Bien qu'il soit possible de précéder chaque ligne du caractère de commentaire ***inline***, pour les gros pavés de texte, le commentaire multi-ligne reste plus approprié. Là encore j'ai utilisé le modèle le plus courant qui est le suivant.

```
/* Comment Block on one line */

/**
 * Multi-ligne
 * Comment
 **/
 
/**
DIRTY
COMMENT
BLOCK
**/
```


### 2.2. Les entités :

Dans les univers **Unix** et **Linux**, tout est fichier, même les dossiers.
Fonctionnellement parlant, en revanche on distingue les deux entités :

* Les fichiers : ``FILES``
* Les dossiers : ``FOLDERS``

L'entité ``FILES`` sert à indiquer les fichiers à exclure.

L'entité ``FOLDERS`` sert à indiquer les dossiers à exclure.

Exclure un dossier comprend donc l'ensemble des fichiers et sous-dossier qu'il contient.


### 2.3. Les blocks d'instructions :

Tout comme **NGINX**, les instructions de configuration sont placées dans une structure constituant un **bloc**.
Un bloc se présente sous la forme suivante :

```
condition_src condition_value {
    (...)
    FILES {
        file_name.ext
    }
    (...)
}
```

Pour créer une règle d'exclusion, il faut créer un bloc muni d'une condition de réalisation puis de lister les valeurs à exclure pour la ou les entités désirées : ``FILES`` et/ou ``FOLDER``.


### 2.4. Les conditions

Le système de condition porte sur les données disponibles dans la variable super-globale ``$_SERVER`` disponible sous **Apache** (SAPI **apache**) et **NGINX** (SAPI **FPM-FastCGI**) et en ligne de commande (SAPI **CLI**).

> SAPI : Serveur Application Programming Interface.

Dans la formule suivante : ``condition_src condition_value``, ``condition_src`` prendra la valeur d'une clé existante dans la variable super-globale ``$_SERVER``, tandis que ``condition_value`` sera la valeur à remplir pour traiter l'ensemble des instructions enfants à ce bloc d'instruction.


### 2.5. Expressions Réguliéres

L'ensemble des valeurs saisies, qu'il s'agisse d'une valeur de condition, d'un nom de fichier ou dossier à exclure, celles-ci sont traitées en tant que valeur exacte.
Il est possible de rendre ces valeurs plus souples en utilisant les expressions régulières. Pour transformer une valeur exacte en expression régulière, il suffit de la précéder à l'aide du caractère tilde ``~``.


## Annexe 1 - Server keys

Ci-dessous, la liste compléte des clés admise pour définir une condition.

> La disponibilité des clés pour Apache n'ont pas été évaluées.

```
                                 +-----------------------+
                                 |          SAPI         |
+--------------------------------+-------+-------+-------+-------------------------------------------
|           NAME OF KEY          | APACH | NGINX | SHELL | DESCRIPTION (Value sample)
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           USER |       |   X   |   X   | www-data
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           HOME |       |   X   |   X   | /var/www
+--------------------------------+-------+-------+-------+-------------------------------------------
|                      FCGI_ROLE |       |   X   |       | RESPONDER
+--------------------------------+-------+-------+-------+-------------------------------------------
|                      PATH_INFO |       |   X   |       |
+--------------------------------+-------+-------+-------+-------------------------------------------
|                SCRIPT_FILENAME |       |   X   |   X   | /var/www/browse.php
+--------------------------------+-------+-------+-------+-------------------------------------------
|                   QUERY_STRING |       |   X   |       |
+--------------------------------+-------+-------+-------+-------------------------------------------
|                 REQUEST_METHOD |       |   X   |       | GET
+--------------------------------+-------+-------+-------+-------------------------------------------
|                   CONTENT_TYPE |       |   X   |       |
+--------------------------------+-------+-------+-------+-------------------------------------------
|                 CONTENT_LENGTH |       |   X   |       |
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    SCRIPT_NAME |       |   X   |   X   | /browse.php
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    REQUEST_URI |       |   X   |       | /PHP.Classes/SYSLang.V.1.0/
+--------------------------------+-------+-------+-------+-------------------------------------------
|                   DOCUMENT_URI |       |   X   |       | /browse.php
+--------------------------------+-------+-------+-------+-------------------------------------------
|                  DOCUMENT_ROOT |       |   X   |   X   | /var/www
+--------------------------------+-------+-------+-------+-------------------------------------------
|                SERVER_PROTOCOL |       |   X   |       | HTTP/1.1
+--------------------------------+-------+-------+-------+-------------------------------------------
|              GATEWAY_INTERFACE |       |   X   |       | CGI/1.1
+--------------------------------+-------+-------+-------+-------------------------------------------
|                SERVER_SOFTWARE |       |   X   |       | nginx/1.6.2
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    REMOTE_ADDR |       |   X   |       | 74.123.43.22
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    REMOTE_PORT |       |   X   |       | 53211
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    SERVER_ADDR |       |   X   |       | 192.168.1.253
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    SERVER_PORT |       |   X   |       | 80
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    SERVER_NAME |       |   X   |       | neoblaster.fr
+--------------------------------+-------+-------+-------+-------------------------------------------
|                REDIRECT_STATUS |       |   X   |       | 200
+--------------------------------+-------+-------+-------+-------------------------------------------
|                            CWD |       |   X   |       | Custom server param sent by FAST_PARAM
+--------------------------------+-------+-------+-------+-------------------------------------------
|                      HTTP_HOST |       |   X   |       | neoblaster.fr
+--------------------------------+-------+-------+-------+-------------------------------------------
|                HTTP_CONNECTION |       |   X   |       | keep-alive
+--------------------------------+-------+-------+-------+-------------------------------------------
| HTTP_UPGRADE_INSECURE_REQUESTS |       |   X   |       | 1
+--------------------------------+-------+-------+-------+-------------------------------------------
|                HTTP_USER_AGENT |       |   X   |       | Mozilla/5.0 (Windows NT 6.1; WOW64) (...)
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    HTTP_ACCEPT |       |   X   |       |
+--------------------------------+-------+-------+-------+-------------------------------------------
|                       HTTP_DNT |       |   X   |       | 1
+--------------------------------+-------+-------+-------+-------------------------------------------
|           HTTP_ACCEPT_ENCODING |       |   X   |       | gzip, deflate, sdch
+--------------------------------+-------+-------+-------+-------------------------------------------
|           HTTP_ACCEPT_LANGUAGE |       |   X   |       | fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4
+--------------------------------+-------+-------+-------+-------------------------------------------
|                    HTTP_COOKIE |       |   X   |       | SYSLang_LANG=fr-FR
+--------------------------------+-------+-------+-------+-------------------------------------------
|                       PHP_SELF |       |   X   |   X   | /browse.php
+--------------------------------+-------+-------+-------+-------------------------------------------
|             REQUEST_TIME_FLOAT |       |   X   |   X   | 1485468784.8032
+--------------------------------+-------+-------+-------+-------------------------------------------
|                   REQUEST_TIME |       |   X   |   X   | 1485468784
+--------------------------------+-------+-------+-------+-------------------------------------------
|                 XDG_SESSION_ID |       |       |   X   | c2
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           TERM |       |       |   X   | xterm
+--------------------------------+-------+-------+-------+-------------------------------------------
|                          SHELL |       |       |   X   | /bin/bash
+--------------------------------+-------+-------+-------+-------------------------------------------
|                     SSH_CLIENT |       |       |   X   | 74.123.43.22 50163 22
+--------------------------------+-------+-------+-------+-------------------------------------------
|                        SSH_TTY |       |       |   X   | /dev/pts/0
+--------------------------------+-------+-------+-------+-------------------------------------------
|                      LS_COLORS |       |       |   X   | rs=0:di=01;34:ln=01;36:mh=00:pi=40; (...)
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           MAIL |       |       |   X   | /var/mail/pi
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           PATH |       |       |   X   | /usr/local/sbin:/usr/local/bin:/usr/sbin
+--------------------------------+-------+-------+-------+-------------------------------------------
|                            PWD |       |       |   X   | /var/www/Prepends
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           LANG |       |       |   X   | en_GB.UTF-8
+--------------------------------+-------+-------+-------+-------------------------------------------
|                          SHLVL |       |       |   X   | 1
+--------------------------------+-------+-------+-------+-------------------------------------------
|                        LOGNAME |       |       |   X   | pi
+--------------------------------+-------+-------+-------+-------------------------------------------
|                 SSH_CONNECTION |       |       |   X   | 74.123.43.22 50163 192.168.1.253 22
+--------------------------------+-------+-------+-------+-------------------------------------------
|                XDG_RUNTIME_DIR |       |       |   X   | /run/user/1000
+--------------------------------+-------+-------+-------+-------------------------------------------
|                         OLDPWD |       |       |   X   | /var/www
+--------------------------------+-------+-------+-------+-------------------------------------------
|                              _ |       |       |   X   | /usr/bin/php
+--------------------------------+-------+-------+-------+-------------------------------------------
|                PATH_TRANSLATED |       |       |   X   | auto_prepend_files.php
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           argv |       |       |   X   | ARRAY
+--------------------------------+-------+-------+-------+-------------------------------------------
|                           argc |       |       |   X   | 1
+--------------------------------+-------+-------+-------+-------------------------------------------
`













