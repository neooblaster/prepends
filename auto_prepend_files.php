<?php
/**
 * auto_prepend_files.php
 *
 * Par le biais de ce script auto prepended depuis la configuration de `php.ini`, celui-ci permet de charger
 * autant de fichiers que nécessaire qui sont déposés dans le dossier courant, sans qu'il soit nécessaire
 * de les ajouter dans la directive `auto_prepend_file` du fichier `php.ini`.
 *
 * Description Fonctionnelle :
 *
 * 1. Création d'une variable unique $_PREPEND pour effacer toutes traces d'execution du présent script.
 *
 * 2. Traitement
 *  * 2.1. S'ajouter en tant que fichier exclus
 *  * 2.2. Si un fichier .ignore existe, on le parse
 *  * 2.3. On parcours tous les dossiers/fichiers dans l'emplacement courant
 *  * 2.4. Effacer toutes les traces (variables & fichier temporaire)
 *
 * @author    Nicolas DUPRE
 * @release   11.12.2019
 * @version   1.3.4
 * @package   Index
 */


/**
 * @var array $_PREPEND Environnement de travail du script afin de laisser pur la global $GLOBALS
 */
$_PREPEND = [
    /**
     * boolean Active le mode debuggage permettant l'affichage des sorties
     */
    "debug" => false,

    /**
     * array Règles d'exclusions
     */
    "ignore" => [
        /**
         * array Liste des dossiers à exclure
         */
        "folders" => [],
        "folders_regexp" => [],
        /**
         * array Liste des fichiers à exclure
         */
        "files" => [],
        "files_regexp" => []
    ]
];


/**
 * Affiche le message demandé dans le flux de sortie
 */
function apf_stdout($message, $stop = false){
    global $_PREPEND;

    $_PREPEND["output"] = fopen("php://output", "r");

    if(@$_PREPEND["debug"]){
        fputs($_PREPEND["output"], $message);

        if($stop) exit;
    }
}

/**
 * Lit de manière récursive le dossier indiqué et inclus les fichiers trouvé
 */
function apf_read_folder($full_path){
    global $_PREPEND;
    $folders = scandir($full_path);

    /** Parcourir le résultat obtenu */
    foreach ($folders as $fkey => $fvalue){
        /** Si l'entrée ne commence pas par un point */
        if(!preg_match("#^\.#", $fvalue)){

            /** S'il s'agit d'un dossier */
            if(is_dir("$full_path/$fvalue")){
                apf_stdout("--> CHECKING DIR $fvalue" . PHP_EOL);

                /** S'il n'est pas explicitement exclus */
                if(!in_array($fvalue, $_PREPEND["ignore"]["folders"])){
                    $allowed = true;

                    /** S'il n'est pas exclus par RegExp */
                    foreach ($_PREPEND["ignore"]["folders_regexp"] as $exclude_pattern){
                        apf_stdout("--> TESTING REGEXP '$exclude_pattern'" . PHP_EOL);
                        if(preg_match("#$exclude_pattern#", $fvalue)){
                            $allowed = false;
                            break;
                        }

                    }

                    if($allowed) apf_read_folder("$full_path/$fvalue");
                }
            }
            /** S'il s'agit d'un fichier */
            else {
                apf_stdout("--> CHECKING FILE $fvalue" . PHP_EOL);

                /** S'il n'est pas explicitement exclus */
                if(!in_array($fvalue, $_PREPEND["ignore"]["files"])){
                    $allowed = true;

                    /** S'il n'est pas exclus par RegExp */
                    foreach ($_PREPEND["ignore"]["files_regexp"] as $exclude_pattern){
                        if(preg_match("#$exclude_pattern#", $fvalue)){
                            apf_stdout("--> TESTING REGEXP '$exclude_pattern'" . PHP_EOL);
                            $allowed = false;
                            break;
                        }
                    }

                    if($allowed) require_once "$full_path/$fvalue";
                }
            }
        }
        apf_stdout(PHP_EOL);
    }
}


/**
 * Activation du niveau d'erreur à "afficher tout"
 */
if($_PREPEND["debug"]) error_reporting(E_ALL);


/**
 * S'auto-exclure des fichiers
 *
 * Permet tout renommage sans modification du script
 * Empêche de créer une boucle infinie
 */
$_PREPEND["ignore"]["files"][] = basename(__FILE__);


/**
 * Processing
 */
apf_stdout("<pre>");

// Traitement du fichier .ignore
if(file_exists(__DIR__ . "/.ignore")){
    apf_stdout("--> .ignore FILE PROCESSING" . PHP_EOL);

    if(!file_exists(__DIR__ . "/.tmp")) mkdir(__DIR__ . "/.tmp", 0775);

    $_PREPEND["skip_file_cleansing"] = false;
    $_PREPEND["ignore_file"] = fopen(__DIR__ . "/.ignore", "r");
    $_PREPEND["tmp_file_name"] = md5(file_get_contents(__DIR__ . "/.ignore"));

    /**
     * Inutile de retraiter un fichier qui ne change que rarement
     * Evite les conflits d'écriture en cas de traitement simultané
     * Permet la sauvegarde des ancienne configuration
     */
    if(file_exists(__DIR__ . "/.tmp/" . $_PREPEND["tmp_file_name"])) $_PREPEND["skip_file_cleansing"] = true;

    /**
     * Nettoyage du fichier .ignore
     */
    if(!$_PREPEND["skip_file_cleansing"]){
        /** Création d'un fichier temporaire de destination */
        $_PREPEND["ignore_tmp_file"] = fopen(__DIR__ . "/.tmp/" . $_PREPEND["tmp_file_name"], "w+");

        /** Rechercher le BREAK_SIGNAL dans le fichier d'origine */
        while($_PREPEND["buffer"] = fgets($_PREPEND["ignore_file"])){
            if(preg_match("/#\s*BREAK_SIGNAL/", $_PREPEND["buffer"])){
               fclose($_PREPEND["ignore_tmp_file"]);
               break;
            } else {
                fputs($_PREPEND["ignore_tmp_file"], $_PREPEND["buffer"]);
            }
        }
        fclose($_PREPEND["ignore_file"]);

        /** Suppression des commentaires */
        $_PREPEND["ignore_cfg"] = file_get_contents(__DIR__ . "/.tmp/" . $_PREPEND["tmp_file_name"]);

        apf_stdout("--> FILE BEFORE COMMENTS CLEANSING" . PHP_EOL);
        apf_stdout($_PREPEND["ignore_cfg"]);

        $_PREPEND["ignore_cfg"] = preg_replace("/^\s*(#|;).*/mi", "", $_PREPEND["ignore_cfg"]);
        $_PREPEND["ignore_cfg"] = preg_replace("#//.*#mi", "", $_PREPEND["ignore_cfg"]);
        $_PREPEND["ignore_cfg"] = preg_replace("#\/+\*+(.|\s)*\*+\/+#miU", "", $_PREPEND["ignore_cfg"]);

        apf_stdout("--> FILE AFTER COMMENTS CLEANSING" . PHP_EOL);
        apf_stdout($_PREPEND["ignore_cfg"]);

        file_put_contents(__DIR__ . "/.tmp/" . $_PREPEND["tmp_file_name"], $_PREPEND["ignore_cfg"]);
    }

    /** Lecture des règles */
    $_PREPEND["ignore_file_light"] = fopen(__DIR__ . "/.tmp/" . $_PREPEND["tmp_file_name"], "r");
    $_PREPEND["current_level"] = 0;
    $_PREPEND["accept_level"] = Array(true); // Niveau 0 est otujours admis, car vaut pour le wildcard *
    $_PREPEND["in_folders_block"] = false;
    $_PREPEND["in_files_block"] = false;

    while($_PREPEND["buffer"] = fgets($_PREPEND["ignore_file_light"])){
        /** Supprimer le retour chariot */
        $_PREPEND["buffer"] = str_replace("\n", "", $_PREPEND["buffer"]);

        /** RAZ du flag indiquant de ne pas traiter cette ligne de buffer car vaut pour instruction */
        $_PREPEND["skip_buffer"] = false;

        /** Si la ligne n'est pas vide */
        if(!preg_match("#^\s*$#", $_PREPEND["buffer"])){
            apf_stdout("BUFFER :: #$_PREPEND[buffer]#" . PHP_EOL);

            /** Vérifier s'il s'agit d'un groupe FILES */
            if(preg_match("#^\s*FILES\s*\{#i", $_PREPEND["buffer"]) && $_PREPEND["accept_level"][$_PREPEND["current_level"]]){
                apf_stdout("--> BLOCK FILE FOUND & LEVEL ALLOW;".PHP_EOL);
                $_PREPEND["in_files_block"] = true;
                $_PREPEND["skip_buffer"] = true;
            }

            /** Vérifier s'il s'agit d'un groupe FOLDER et que celui-ci est approuvé dans son niveau d'imbrication */
            if(preg_match("#^\s*FOLDERS\s*\{#i", $_PREPEND["buffer"])){
                apf_stdout("--> BLOCK FOLDER FOUND & LEVEL ALLOW;".PHP_EOL);
                $_PREPEND["in_folders_block"] = true;
                $_PREPEND["skip_buffer"] = true;
            }

            /** Vérifier s'il s'agit d'une règle d'exclusion (instruction) */
            if(preg_match("#^\s*([a-zA-Z_]+)\s+([a-zA-Z0-9-_.\s\/\(\);,=:\*^$?!~]+)\s+\{#", $_PREPEND["buffer"], $_PREPEND["matches"])){
                apf_stdout("--> INSTRUCTION FOUND;" . PHP_EOL);

                /** On entre dans un niveau/sous/niveau */
                apf_stdout("--> INCREASE LEVEL FROM $_PREPEND[current_level]");
                $_PREPEND["current_level"]++;
                apf_stdout(" to $_PREPEND[current_level]" . PHP_EOL);

                /** Translation de variable pour "simplifier" */
                $_PREPEND["key"] = $_PREPEND["matches"][1];
                $_PREPEND["value"] = $_PREPEND["matches"][2];

                if(array_key_exists($_PREPEND["key"], $_SERVER)){
                    apf_stdout("--> KEY EXIST IN &amp;_SERVER;" . PHP_EOL);

                    /** Si la valeur commence par un tilde, alors c'est une RegExp */
                    if(preg_match("#^~#", $_PREPEND["value"])){
                        apf_stdout("--> VALUE READ AS REGEXP;" . PHP_EOL);
                        /** Suppression du tilde */
                        $_PREPEND["value"] = preg_replace("#^~#", "", $_PREPEND["value"], 1);

                        if(preg_match("#$_PREPEND[value]#", $_SERVER[$_PREPEND["key"]])){
                            apf_stdout("--> &amp;_SERVER VALUE MATCH WITH REGEXP PATTERN;");
                            $_PREPEND["accept_level"][$_PREPEND["current_level"]] = true;
                        } else {
                            apf_stdout("--> &amp;_SERVER VALUE DOES NOT MATCH WITH REGEXP PATTERN;");
                            $_PREPEND["accept_level"][$_PREPEND["current_level"]] = false;
                        }
                    }
                    /** Sinon si la valeur est exactement celle attendue */
                    else if ($_SERVER[$_PREPEND["key"]] == $_PREPEND["value"]) {
                        apf_stdout("--> VALUE READ AS STRING;" . PHP_EOL);
                        apf_stdout("--> VALUE EXACTLY EQUAL;" . PHP_EOL);
                        $_PREPEND["accept_level"][$_PREPEND["current_level"]] = true;
                    }
                    /** Sinon, ignorer le block */
                    else {
                        apf_stdout("--> NOT CORRESPONDING; IGNORE BLOCK" . PHP_EOL);
                        $_PREPEND["accept_level"][$_PREPEND["current_level"]] = false;
                    }

                } else {
                    $_PREPEND["accept_level"][$_PREPEND["current_level"]] = false;
                }

                $_PREPEND["skip_buffer"] = true;
            }

            /** Vérifier s'il s'agit d'une fermeture de block */
            if(preg_match("#^\s*\}\s*$#", $_PREPEND["buffer"])){
                apf_stdout("--> BLOCK END FOUND - CLOSE LEVEL;" . PHP_EOL);

                /** Gestion de niveau s'il ne s'agisssait pas d'un block FOLDERS ou FILES */
                if(!$_PREPEND["in_files_block"] && !$_PREPEND["in_folders_block"]){
                    $_PREPEND["accept_level"] = [true];

                    $_PREPEND["current_level"]--;
                    if ($_PREPEND["current_level"] < 0) $_PREPEND["current_level"] = 0;
                }

                /** Si nous étions dans un block FILES ou FOLDERS, il est cloturé, on n'enregistre plus */
                if($_PREPEND["in_files_block"]) $_PREPEND["in_files_block"] = false;
                if($_PREPEND["in_folders_block"]) $_PREPEND["in_folders_block"] = false;

                $_PREPEND["skip_buffer"] = true;
            }

            /** Traitement du buffer */
            if(!$_PREPEND["skip_buffer"]){
                if ($_PREPEND["accept_level"][$_PREPEND["current_level"]]) {
                    apf_stdout("--> TEXT LINE, PROCESS;" . PHP_EOL);

                    preg_match("#^\s*(.*)\s*$#i", $_PREPEND["buffer"], $_PREPEND["matches"]);

                    /** Permet d'ignorer les lignes vide entre deux valeurs au sein du même block */
                    if(count($_PREPEND["matches"] >= 2)){
                        apf_stdout("--> CHECK FOR EXCLUSION;" . PHP_EOL);

                        $regexp_store = "";

                        if(preg_match("#^~#", $_PREPEND["matches"][1])){
                            $regexp_store = "_regexp";
                            $_PREPEND["matches"][1] = preg_replace("#^~#", "", $_PREPEND["matches"][1]);
                        }

                        if($_PREPEND["in_files_block"]){
                            apf_stdout("--> LINE ADD TO FILES EXCLUSION;" . PHP_EOL);
                            $_PREPEND["ignore"]["files$regexp_store"][] = $_PREPEND["matches"][1];
                        } else if($_PREPEND["in_folders_block"]) {
                            apf_stdout("--> LINE ADD TO FOLDERS EXCLUSION;" . PHP_EOL);
                            $_PREPEND["ignore"]["folders$regexp_store"][] = $_PREPEND["matches"][1];
                        } else {
                            apf_stdout("--> LINE SKIPPED;" . PHP_EOL);
                        }
                    }
                } else {
                    apf_stdout("--> CURRENT LEVEL NOT ALLOWED. IGNORE BUFFER");
                }
            }

            apf_stdout(PHP_EOL . PHP_EOL);
        }

    }

    fclose($_PREPEND["ignore_file_light"]);
}

apf_stdout("RECEIVED SERVER DATA : " . PHP_EOL);
apf_stdout(print_r($_SERVER, true));

apf_stdout("IGNORE RULES RESULT : " . PHP_EOL);
apf_stdout(print_r($_PREPEND["ignore"], true));

apf_stdout("AUTO_PREPEND_FILES SCRIPT &amp;_PREPEND FOOTPRINT : " . PHP_EOL);
apf_stdout(print_r($GLOBALS["_PREPEND"], true));


// Lecture du dossier courant
apf_read_folder(__DIR__);


// On debug, stop the process
if(@$_PREPEND["debug"]) exit;


// Cleansing
@unlink(__DIR__ . "/.ignore_tmp");
unset($GLOBALS["_PREPEND"]);
unset($GLOBALS["regexp_store"]);


if(@$_PREPEND["debug"]) print_r($GLOBALS);