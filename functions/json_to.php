<?php
/**
 * json_to.php
 *
 * Assimile les données JSON en donnée PHP Exploitable (constante ou variables)
 * Note : Dans le cas où la récurivité est désactivée et que la valeur est un objet JSON ou une liste :
 *  * Si `variable` est retenue : La variable sera un Array
 *  * Si `constant` est retenue : La variable est ignorée
 *
 * @author    Nicolas DUPRE
 * @release   21/09/2017
 * @version   1.0.0
 * @package   Prepend
 */

/**
 * Assimile les données JSON en donnée PHP Exploitable (constante ou variables)
 *
 * @param string $json_string     Chaine JSON à traiter
 * @param string $output_type     Type de donnée en sortie (constant, variable)
 * @param bool   $recursive       Traitement récursif de la structure JSON donnée
 * @param string $resurive_link   Caractère de liaison entre les différents niveaux
 *
 * @return bool
 */
function json_to (
    $json_string,
    $output_type = 'constant',
    $recursive = true,
    $resurive_link = '_'
) {
    /** 1. Suppression des éventuels commentaire présent dans la chaine */
    // Sécurisation du modèle commentaire dans les block textes
    $json_string = preg_replace_callback("#[\"']\K(.*)[\"']#U", function($matches){
        return preg_replace("#\/#", "&slash;", $matches[0]);
    }, $json_string);

    // Suppression des commentaires en ligne
    $json_string = preg_replace("#\/\/.*#", "", $json_string);
    // Suppression des commentaires en block
    $json_string = preg_replace("#\/\*+(.*\s)*\*+\/#U", "", $json_string);
    // Suppression des lignes vides
    $json_string = preg_replace("#^\s*\n#", "", $json_string);

    // Restitution des slashs modifiés
    $json_string = preg_replace("#&slash;#", "/", $json_string);


    /** 2. Contrôler la syntaxe JSON */
    $json_array = json_decode($json_string, true);

    if (json_last_error()) {
        trigger_error(
            "JSON String supplied is not valid : " . json_last_error_msg() . " in : "
            . "<span style='color: red;'>" . $json_string . "</span>",
            E_USER_WARNING
        );

        return false;
    }


    /** 3. Déclaration d'un fonction interne */
    if (!function_exists('json_to_read_array')) {
        /**
         * @param array       $json_array       Tableau à traiter
         * @param string      $output_type      Type de donnée en sortie (constant, variable)
         * @param bool        $recursive        Traitement récursif du tableau
         * @param string      $recursive_link   Caractère de liaison entre les différents niveaux
         * @param null|string $backtrace
         */
        function json_to_read_array (
            $json_array,
            $output_type,
            $recursive,
            $recursive_link,
            $backtrace = null
        ) {
            /** Lecture du tableau */
            foreach ($json_array as $jkey => $value) {
                $name = ($backtrace !== null) ? ($backtrace . $recursive_link . $jkey) : ($jkey);

                if (is_array($value) && $recursive) {
                    json_to_read_array($value, $output_type, $recursive, $recursive_link, $name);
                } else {
                    switch (strtolower($output_type)) {
                        case 'variable':
                            $GLOBALS[$name] = $value;
                            break;
                        case 'constant':
                            define($name, $value);
                            break;
                    }
                }
            }
        }
    }


    /** 4. Parcourir le tableau obtenu */
    json_to_read_array($json_array, $output_type, $recursive, $resurive_link);

    return true;
}
