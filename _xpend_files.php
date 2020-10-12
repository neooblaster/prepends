<?php
/**
 * _xpend_files.php
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
 * @release   12.10.2020
 * @version   2.0.0
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
     * string  Indicates if "caller" is "prepend" or "append" interface
     */
    "caller" => $_PREPEND['caller'],

    /**
     * array Règles d'exclusions
     */
    "exclude" => [
        /**
         * array Liste des dossiers à exclure
         */
        "folders" => [],
        "folders_regexp" => [],
        /**
         * array Liste des fichiers à exclure
         */
        "files" => [
            // Self exclusion
            "_xpend_files.php",
            "_prepend.php",
            "_append.php"
        ],
        "files_regexp" => []
    ],

    /**
     * array Règles d'inclusion
     */
    "include" => [
        /**
         * array Liste des dossiers à inclure
         */
        "folders" => [],
        "folders_regexp" => [],
        /**
         * array Liste des fichiers à inclure
         */
        "files" => [],
        "files_regexp" => []
    ]
];


/**
 * Affiche le message demandé dans le flux de sortie
 */
if (!function_exists('apf_stdout')) {
    function apf_stdout($message, $stop = false){
        global $_PREPEND;

        $_PREPEND["output"] = fopen("php://output", "r");

        if(@$_PREPEND["debug"]){
            fputs($_PREPEND["output"], $message . PHP_EOL);

            if($stop) exit;
        }
    }
}

/**
 * Lit de manière récursive le dossier indiqué et inclus les fichiers trouvé
 */
if (!function_exists("apf_read_folder")) {
    function apf_read_folder($full_path){
        global $_PREPEND;
        $folders = scandir($full_path);

        /** Parcourir le résultat obtenu */
        foreach ($folders as $fkey => $fvalue){
            /** Si l'entrée ne commence pas par un point */
            if(!preg_match("#^\.#", $fvalue)){
                apf_stdout("--> CHECKING FILE $fvalue");

                /** S'il s'agit d'un dossier */
                if(is_dir("$full_path/$fvalue")){
                    // If allowed, read recursively
                    if (apf_check_allowed($fvalue, "folders")) apf_read_folder("$full_path/$fvalue");
                }
                /** S'il s'agit d'un fichier */
                else {
                    // If allowed, include file
                    if (apf_check_allowed($fvalue, "files")) require "$full_path/$fvalue";
                }
            }
        }
    }
}

/**
 * Check if entity is allowed (file or folder)
 */
if (!function_exists("apf_check_allowed")) {
    function apf_check_allowed ($file, $type)
    {
        global $_PREPEND;
        $allowed = false;

        // Check if file/folder is included
        // -- Check as it
        if (in_array($file, $_PREPEND["include"][$type])) {
            $allowed = true;
        }
        // -- Check as RegExp
        else {
            foreach ($_PREPEND["include"]["${type}_regexp"] as $include_pattern) {
                if (preg_match("#$include_pattern#", $file)) {
                    $allowed = true;
                    break;
                }
            }
        }

        // Check if file/folder is excluded
        // -- If no include rule found, check if excluded
        if (!$allowed) {
            // -- If not excluded as it, consider folder as allowed
            //    until regexp can excluded it
            if (!in_array($file, $_PREPEND['exclude'][$type])) {
                $allowed = true;

                // -- Check regexp
                foreach ($_PREPEND['exclude']["${type}_regexp"] as $exclude_pattern) {
                    if (preg_match("#$exclude_pattern#", $file)) {
                        $allowed = false;
                        break;
                    }
                }
            }
        }

        // If allowed, read recursively
        return $allowed;
    }
}



/**
 * Activation du niveau d'erreur à "afficher tout"
 */
if($_PREPEND["debug"]) error_reporting(E_ALL);



/**
 * Processing
 */
apf_stdout("<pre>");

// Traitement du fichier .config
$_PREPEND["config_file"] = ($_PREPEND["debug"]) ? ".config_dev" : ".config";
if(file_exists(__DIR__ . "/" . $_PREPEND["config_file"])){
    apf_stdout("--> " . $_PREPEND["config_file"] . " FILE PROCESSING");

    if(!file_exists(__DIR__ . "/.cache")) mkdir(__DIR__ . "/.cache", 0775);

    $_PREPEND["skip_file_cleansing"] = false;
    $_PREPEND["ignore_file"] = fopen(__DIR__ . "/" . $_PREPEND["config_file"], "r");
    $_PREPEND["tmp_file_name"] = md5(file_get_contents(__DIR__ . "/" . $_PREPEND["config_file"]));

    /**
     * Inutile de retraiter un fichier qui ne change que rarement
     * Evite les conflits d'écriture en cas de traitement simultané
     * Permet la sauvegarde des ancienne configuration
     */
    if(file_exists(__DIR__ . "/.cache/" . $_PREPEND["tmp_file_name"])) $_PREPEND["skip_file_cleansing"] = true;

    /**
     * Nettoyage du fichier .oonfig
     */
    if(!$_PREPEND["skip_file_cleansing"]){
        /** Création d'un fichier temporaire de destination */
        $_PREPEND["ignore_tmp_file"] = fopen(__DIR__ . "/.cache/" . $_PREPEND["tmp_file_name"], "w+");

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
        $_PREPEND["ignore_cfg"] = file_get_contents(__DIR__ . "/.cache/" . $_PREPEND["tmp_file_name"]);

        apf_stdout("--> FILE BEFORE COMMENTS CLEANSING");
        apf_stdout($_PREPEND["ignore_cfg"]);

        $_PREPEND["ignore_cfg"] = preg_replace("/^\s*(#|;).*/mi", "", $_PREPEND["ignore_cfg"]);
        $_PREPEND["ignore_cfg"] = preg_replace("#//.*#mi", "", $_PREPEND["ignore_cfg"]);
        $_PREPEND["ignore_cfg"] = preg_replace("#\/+\*+(.|\s)*\*+\/+#miU", "", $_PREPEND["ignore_cfg"]);

        apf_stdout("--> FILE AFTER COMMENTS CLEANSING");
        apf_stdout($_PREPEND["ignore_cfg"]);

        file_put_contents(__DIR__ . "/.cache/" . $_PREPEND["tmp_file_name"], $_PREPEND["ignore_cfg"]);
    }

    /** Lecture des règles */
    $_PREPEND["ignore_file_light"] = fopen(__DIR__ . "/.cache/" . $_PREPEND["tmp_file_name"], "r");
    $_PREPEND["current_level"] = 0;
    $_PREPEND["accept_level"] = Array(true); // Niveau 0 est toujours admis, car vaut pour le wildcard *
    $_PREPEND["in_prepend_block"] = false;
    $_PREPEND["in_append_block"] = false;
    $_PREPEND["in_folders_block"] = false;
    $_PREPEND["in_files_block"] = false;
    $_PREPEND["in_excl_block"] = false;
    $_PREPEND["in_incl_block"] = false;
    $_PREPEND["block_openning_order"] = [];

    while($_PREPEND["buffer"] = fgets($_PREPEND["ignore_file_light"])){
        /** Supprimer le retour chariot */
        $_PREPEND["buffer"] = str_replace("\n", "", $_PREPEND["buffer"]);

        /** RAZ du flag indiquant de ne pas traiter cette ligne de buffer car vaut pour instruction */
        $_PREPEND["skip_buffer"] = false;

        /** Si la ligne n'est pas vide */
        if(!preg_match("#^\s*$#", $_PREPEND["buffer"])){
            apf_stdout("BUFFER :: #$_PREPEND[buffer]#");

            // @TODO : refacto contrôle avec fonction
            
            /** Vérifier s'il s'agit d'un groupe FILES */
            if(preg_match("#^\s*FILES\s*\{#i", $_PREPEND["buffer"]) && $_PREPEND["accept_level"][$_PREPEND["current_level"]]){
                if($_PREPEND["in_files_block"]) apf_stdout("FILES block already opennend", true);
                apf_stdout("--> BLOCK FILE FOUND & LEVEL ALLOW;");
                $_PREPEND["in_files_block"] = true;
                $_PREPEND["skip_buffer"] = true;
                $_PREPEND["block_openning_order"][] = "FILES";
            }

            /** Vérifier s'il s'agit d'un groupe FOLDER et que celui-ci est approuvé dans son niveau d'imbrication */
            if(preg_match("#^\s*FOLDERS\s*\{#i", $_PREPEND["buffer"]) && $_PREPEND["accept_level"][$_PREPEND["current_level"]]){
                if($_PREPEND["in_folders_block"]) apf_stdout("FOLDERS block already opennend", true);
                apf_stdout("--> BLOCK FOLDER FOUND & LEVEL ALLOW;");
                $_PREPEND["in_folders_block"] = true;
                $_PREPEND["skip_buffer"] = true;
                $_PREPEND["block_openning_order"][] = "FOLDERS";
            }

            /** Vérifier s'il s'agit d'un groupe PREPEND **/
            if (preg_match("#^\s*PREPEND\s*\{#i", $_PREPEND["buffer"]) && $_PREPEND["accept_level"][$_PREPEND["current_level"]]) {
                if($_PREPEND["in_prepend_block"]) apf_stdout("FILES block already opennend", true);
                apf_stdout("--> BLOCK PREPEND FOUND & LEVEL ALLOW;");
                $_PREPEND["in_prepend_block"] = true;
                $_PREPEND["skip_buffer"] = true;
                $_PREPEND["block_openning_order"][] = "PREPEND";
            }

            /** Vérifier s'il s'agit d'un groupe APPEND **/
            if (preg_match("#^\s*APPEND\s*\{#i", $_PREPEND["buffer"]) && $_PREPEND["accept_level"][$_PREPEND["current_level"]]) {
                if($_PREPEND["in_append_block"]) apf_stdout("APPEND block already opennend", true);
                apf_stdout("--> BLOCK APPEND FOUND & LEVEL ALLOW;");
                $_PREPEND["in_append_block"] = true;
                $_PREPEND["skip_buffer"] = true;
                $_PREPEND["block_openning_order"][] = "APPEND";
            }

            /** Vérifier s'il s'agit d'un groupe INCLUDE **/
            if (preg_match("#^\s*INCLUDE\s*\{#i", $_PREPEND["buffer"]) && $_PREPEND["accept_level"][$_PREPEND["current_level"]]) {
                if($_PREPEND["in_incl_block"]) apf_stdout("INCLUDE block already opennend", true);
                apf_stdout("--> BLOCK INCLUDE FOUND & LEVEL ALLOW;");
                $_PREPEND["in_incl_block"] = true;
                $_PREPEND["skip_buffer"] = true;
                $_PREPEND["block_openning_order"][] = "INCLUDE";
            }

            /** Vérifier s'il s'agit d'un groupe EXCLUDE **/
            if (preg_match("#^\s*EXCLUDE\s*\{#i", $_PREPEND["buffer"])) {
                if($_PREPEND["in_excl_block"]) apf_stdout("EXCLUDE block already opennend", true);
                apf_stdout("--> BLOCK EXCLUDE FOUND & LEVEL ALLOW;");
                $_PREPEND["in_excl_block"] = true;
                $_PREPEND["skip_buffer"] = true;
                $_PREPEND["block_openning_order"][] = "EXCLUDE";
            }

            /** Vérifier s'il s'agit d'une règle d'exclusion (instruction) */
            if(preg_match("#^\s*([a-zA-Z_]+)\s+([a-zA-Z0-9-_.\s\/\(\);,=:\*^$?!~]+)\s+\{#", $_PREPEND["buffer"], $_PREPEND["matches"])){
                apf_stdout("--> INSTRUCTION FOUND;");
                $_PREPEND["block_openning_order"][] = 'INSTRUCTION';

                /** On entre dans un niveau/sous/niveau */
                apf_stdout("--> INCREASE LEVEL FROM $_PREPEND[current_level]");
                $_PREPEND["current_level"]++;
                apf_stdout(" to $_PREPEND[current_level]");

                /** Translation de variable pour "simplifier" */
                $_PREPEND["key"] = $_PREPEND["matches"][1];
                $_PREPEND["value"] = $_PREPEND["matches"][2];

                if(array_key_exists($_PREPEND["key"], $_SERVER)){
                    apf_stdout("--> KEY EXIST IN &amp;_SERVER;");

                    /** Si la valeur commence par un tilde, alors c'est une RegExp */
                    if(preg_match("#^~#", $_PREPEND["value"])){
                        apf_stdout("--> VALUE READ AS REGEXP;");
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
                        apf_stdout("--> VALUE READ AS STRING;");
                        apf_stdout("--> VALUE EXACTLY EQUAL;");
                        $_PREPEND["accept_level"][$_PREPEND["current_level"]] = true;
                    }
                    /** Sinon, ignorer le block */
                    else {
                        apf_stdout("--> NOT CORRESPONDING; IGNORE BLOCK");
                        $_PREPEND["accept_level"][$_PREPEND["current_level"]] = false;
                    }

                } else {
                    $_PREPEND["accept_level"][$_PREPEND["current_level"]] = false;
                }

                $_PREPEND["skip_buffer"] = true;
            }

            /** Vérifier s'il s'agit d'une fermeture de block */
            if(preg_match("#^\s*\}\s*$#", $_PREPEND["buffer"])){
                apf_stdout("--> BLOCK END FOUND - CLOSE LEVEL;");

                /** Gestion de niveau s'il ne s'agisssait pas d'un block FOLDERS/FILES/INCLUDE/EXCLUDE/APPEND/PREPED */
                if (!in_array(end($_PREPEND["block_openning_order"]), ['FILES', 'FOLDERS', 'INCLUDE', 'EXCLUDE', 'PREPEND', 'APPEND'])) {
                    apf_stdout("--> CLOSE rule instruction;");
                    $_PREPEND["accept_level"] = [true];

                    $_PREPEND["current_level"]--;
                    if ($_PREPEND["current_level"] < 0) $_PREPEND["current_level"] = 0;
                }
                /** Sinon adapté la logique en fonction du block fermé **/
                else {
                    apf_stdout("--> CLOSE KEYWORD instruction : " . end($_PREPEND["block_openning_order"]));
                    // @TODO : refacto pour dynamique
                    switch (end($_PREPEND["block_openning_order"])) {
                        case 'FILES':
                            $_PREPEND["in_files_block"] = false;
                            break;
                        case 'FOLDERS':
                            $_PREPEND["in_folders_block"] = false;
                            break;
                        case 'INCLUDE':
                            $_PREPEND["in_incl_block"] = false;
                            break;
                        case 'EXCLUDE':
                            $_PREPEND["in_excl_block"] = false;
                            break;
                        case 'APPEND':
                            $_PREPEND["in_append_block"] = false;
                            break;
                        case 'PREPEND':
                            $_PREPEND["in_prepend_block"] = false;
                            break;
                    }
                    array_pop($_PREPEND["block_openning_order"]);
                }

                $_PREPEND["skip_buffer"] = true;
            }

            /** Traitement du buffer */
            if(!$_PREPEND["skip_buffer"]){
                if ($_PREPEND["accept_level"][$_PREPEND["current_level"]]) {
                    apf_stdout("--> TEXT LINE, PROCESS;");

                    preg_match("#^\s*(.*)\s*$#i", $_PREPEND["buffer"], $_PREPEND["matches"]);

                    /** Permet d'ignorer les lignes vide entre deux valeurs au sein du même block */
                    if(count($_PREPEND["matches"] >= 2)){
                        apf_stdout("--> CHECK FOR TARGET;");

                        $regexp_store = "";

                        // Indicating if we consider FILE/FOLDER rule
                        $_PREPEND["store"] = false;

                        // Check for PREPEND or APPEND (or not set)
                        if (
                              ($_PREPEND["in_append_block"] && $_PREPEND['caller'] === 'append')
                           || ($_PREPEND["in_prepend_block"] && $_PREPEND['caller'] === 'prepend')
                           || (!$_PREPEND["in_append_block"] && !$_PREPEND["in_prepend_block"])
                        ) {
                            $_PREPEND["store"] = true;
                        }

                        // If we have to store FILE/FOLDER rule
                        if ($_PREPEND["store"]) {
                            apf_stdout("--> Store rule :");

                            // If INCLUDE is specified, else EXLCUDE is default (or specified)
                            if ($_PREPEND["in_incl_block"]) {
                                $_PREPEND["target"] = &$_PREPEND['include'];
                            } else {
                                $_PREPEND["target"] = &$_PREPEND['exclude'];
                            }

                            // RegExp Format
                            if(preg_match("#^~#", $_PREPEND["matches"][1])){
                                $regexp_store = "_regexp";
                                $_PREPEND["matches"][1] = preg_replace("#^~#", "", $_PREPEND["matches"][1]);
                            }

                            // If in FILES block :
                            if($_PREPEND["in_files_block"]){
                                apf_stdout("--> LINE ADD TO FILES;");
                                $_PREPEND["target"]["files$regexp_store"][] = $_PREPEND["matches"][1];
                            }
                            //If in FOLDERS block :
                            else if($_PREPEND["in_folders_block"]) {
                                apf_stdout("--> LINE ADD TO FOLDERS;");
                                $_PREPEND["target"]["folders$regexp_store"][] = $_PREPEND["matches"][1];
                            } else {
                                apf_stdout("--> LINE SKIPPED;");
                            }
                            unset($_PREPEND["target"]);
                        }
                    }
                } else {
                    apf_stdout("--> CURRENT LEVEL NOT ALLOWED. IGNORE BUFFER");
                }
            }

            apf_stdout(PHP_EOL);
        }
    }

    fclose($_PREPEND["ignore_file_light"]);
}


apf_stdout("RECEIVED SERVER DATA : ");
apf_stdout(print_r($_SERVER, true));

// Traitement du fichier .include par défaut.

apf_stdout("IGNORE RULES RESULT : ");
apf_stdout(print_r($_PREPEND["exclude"], true));

apf_stdout("INCLUDE RULES RESULT : ");
apf_stdout(print_r($_PREPEND["include"], true));

apf_stdout("AUTO_PREPEND_FILES SCRIPT &amp;_PREPEND FOOTPRINT : ");
apf_stdout(print_r($GLOBALS["_PREPEND"], true));


// Lecture du dossier courant
apf_read_folder(__DIR__);


// Cleansing
@unlink(__DIR__ . "/.config_tmp");
unset($GLOBALS["_PREPEND"]);
unset($GLOBALS["regexp_store"]);

//print_r($GLOBALS);