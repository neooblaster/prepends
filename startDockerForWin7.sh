#!/usr/bin/env bash

#---------------------------------------------------------
#             prepends - startDockerForWin7.sh
#---------------------------------------------------------
# Author  : Nicolas DUPRE
# Version : 0.2.1
# Release : 12.03.2018
#
# Uniqument pour les utilisateurs Windows 7.
# A faire à chaque démarrage de Windows pour démarrer Docker.
#

# Mémoriser le dossier de travail actuel.
CWD=$PWD

# Déclaration des couleurs
CERR="196"
CINF="39"
CWARN="214"
CNOT="247"
CSUC="76"
CKWD="198"
CINP=$CKWD


# Définir pour la session Windows les variables d'env utilisé par Docker.
export DOCKER_TLS_VERIFY="1"
export DOCKER_HOST="tcp://192.168.99.100:2376"
export DOCKER_CERT_PATH="C:\Users\\$USERNAME\.docker\machine\machines\default"
export DOCKER_MACHINE_NAME="default"
export COMPOSE_CONVERT_WINDOWS_PATHS="true"


# Executer le script de démarrage Docker "Quick Start".
cd "$DOCKER_TOOLBOX_INSTALL_PATH"
echo -e "\e[38;5;${CINF}m--> Docker va demarrer dans une petite minute...\e[0m"
nohup start.sh
result="$?"


# Revenir dans le dossier de développement.
cd $CWD


# Contrôler la disponibilité Docker et la commande Make.
if [ "$result" -eq 0 ]; then
    echo -e "\e[38;5;${CSUC}m<-- Docker est prêt!\e[0m"
    sleep 1
    make ready
    sleep 1
    echo -e "\e[38;5;${CINF}m--> Installation de l'environnement de développement...\e[0m"
    make install
    result="$?"
    if [ "$result" -eq 0 ]; then
        echo -e "\e[38;5;${CSUC}m<-- Installation de l'environnement terminée!\e[0m"
    else
        echo -e "\e[38;5;${CERR}m<-- L'installation à échouée \e[38;5;${CKWD}m(-_-)\e[0m"
    fi
else
    echo -e "\e[38;5;${CERR}m<-- Quelque chose s'est mal déroulé \e[38;5;${CKWD}m(-_-)\e[0m"
fi
