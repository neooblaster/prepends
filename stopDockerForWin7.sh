#!/usr/bin/env bash

#---------------------------------------------------------
#            preprends - stopDockerForWin7.sh
#---------------------------------------------------------
# Author  : Nicolas DUPRE
# Version : 0.1.1
# Release : 22.05.2018
#
# Uniqument pour les utilisateurs Windows 7.
# Utilisez le script suivant pour stopper proprement la machine virtuelle Docker.
#

# Stopper la machine virtuelle "Default" créée par Docker.
docker-machine stop default
