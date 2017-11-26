<?php
/**
 * setup.php
 *
 * Inclus le ou les fichiers demandés
 *
 * Si vos fichiers on une nomenclature commune comme l'exemple suivant :
 *  * setup.settings.php
 *  * setup.opendb.php
 *  * setup.openstream.php
 *
 * La notation suivante :
 *
 *  setup(
 *      ['setup.settings.php', 'setup.opendb.php', 'setup.openstream.php'],
 *      "."
 *  );
 *
 *
 * Peu s'écrire ainsi :
 *
 *  setup(
 *      ['settings', 'opendb', 'openstream'],
 *      ".",
 *      "setup.$1.php"
 *  );
 *
 * `$1` sert à indiquer où sera insérée la valeur représentant le fichier dans la liste donnée.
 *
 *
 * @author    Nicolas DUPRE
 * @release   22/09/2017
 * @version   1.0.0
 * @package   Prepend
 */

/**
 * Inclus le ou les fichiers demandés
 *
 * @param array         $setups         Liste des fichiers (setups) à charger
 * @param string        $include_path   Liste des emplacements à parcourir
 * @param null|string   $file_pattern   Peut spécifier un modèle RegExp pour simplifier la notation des fichiers
 * @return bool
 */
function setup (
  $setups,
  $include_path = ".",
  $file_pattern = null
) {
    /** Identification des dossiers d'inclusion */
    $include_path = explode(":", $include_path);

    /** Identification du dossier racine */
    // Indisponible en CLI
    $root = $_SERVER['DOCUMENT_ROOT'];

    /** Charger les setups */
    if (is_array($setups)) {
        foreach ($setups as $skey => $setup) {
            /** Déterminer le modèle de recherche */
            $search = (is_null($file_pattern)) ? $setup : preg_replace('#\$1#', $setup, $file_pattern);

            /** Parcourir les dossiers d'inclusion */
            foreach ($include_path as $ipkey => $path) {
                /** Cas d'un chemin absolu, par rapport au site web et non au serveur */
                if (preg_match("#^/#", $path)) {
                    $full_path = $root . $path;
                }

                /** Cas d'un chemin relatif, avec remonté d'arborescence (../) */
                elseif (preg_match("#^(../)+#", $path)) {
                    /** Récupérer le début de la chaine */
                    preg_match_all("#^(../)+#", $path, $dbdotslashes);

                    /** Compter le nombre d'occurence de ../ */
                    $dbdotslashes_occ = substr_count($dbdotslashes[0][0], "../");

                    $rel_path = $root . @(($_SERVER['REQUEST_URI']) ?: '');

                    /** Vérifier l'existance de l'emplacement correspondant au REQUEST_URI */
                    if (!file_exists($rel_path) || !is_dir($rel_path)) {
                        $rel_path = $root;
                    }

                    for ($i = 0; $i <= $dbdotslashes_occ; $i++) {
                        $rel_path = substr($rel_path, 0, strrpos($rel_path,"/"));
                    }

                    $full_path = $rel_path . '/' . str_replace($dbdotslashes[0][0], "", $path);
                }

                /** Autres cas */
                else {
                    $full_path = $root . @(($_SERVER['REQUEST_URI']) ?: '') . '/' .$path;

                    if (!file_exists($full_path) || !is_dir($full_path)) {
                        $full_path = $root . '/' .$path;
                    }
                }

                /** Supprimer les éventuels ./ et ../ */
                $full_path = str_replace("../", "", $full_path);
                $full_path = str_replace("./", "", $full_path);
                $full_path = str_replace("//", "/", $full_path);

                /** Analyse du dossier */
                $folder = (file_exists($full_path)) ? scandir($full_path) : [];

                /** Recherche le fichier */
                array_map(
                    function ($el) use ($search, $full_path) {
                        if (!preg_match('#^[.]{1,2}$#', $el) && preg_match("#^$search$#", $el)) {
                            include_once "$full_path/$el";

                            /** Sortir les variables de l'include dans le scope global */
                            foreach (get_defined_vars() as $var => $value) {
                                $GLOBALS[$var] = $value;
                            }
                        }
                    }, $folder
                );
            }
        }
    }

    return true;
}
