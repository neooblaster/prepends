## 1.3.0 (2017-11-26)

- [FIXED] Correction de la fonction `setup` qui utilisait le `REQUEST_URI` sans vérification d'existante qui
échouait dans le cas de ré-écriture d'URL.
- [UPDATE] Mise à jour du fichier de configuration `.ignore` pour ignorer la déclaration de constante pour le
**SAPI CLI**.
- [ADDED] Création de la function `array_rmap($callback, &$arr)` permettant l'opération sur les tableaux sans
qu'il soit nécessaire de récupérer le résultat.


## 1.2.1 (2017-10-02)

- [FIXED] Mise à jour du fichier de configuration `.ignore` pour ignorer tous les fichiers **Markdown**.


## 1.2.0 (2017-10-02)

- [FIXED] Correction du setup `setup.const.document_root.php` generant une erreur `E_NOTICE` pour `CLI`.


## 1.1.0 (2017-10-02)

- [CHANGED] Modification du nom du dossier d'inclusion ``Prepends`` par `prepends` afin de s'accorder avec le projet
**[MCOSDE](https://gitlab-gre.viseo.net/MCOScheduler/MCOSDE)**




[!ADDED]:#
[!FIXED]:#
[!CHANGED]:#
[!REMOVED]:#
[!SECURITY]:#
[!DEPRECATED]:#
[!OTER]:#
[!BUGFIX]:#
