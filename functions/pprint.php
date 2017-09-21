<?php
/**
 * pprint.php
 *
 * Affiche le ou les tableaux le stream php://output de manière structurée à l'aide de l'élement HTML <pre>.
 *
 * @author    Nicolas DUPRE
 * @release   21/09/2017
 * @version   1.0.0
 * @package   Prepend
 */
function pprint()
{
    echo "<pre>";

    foreach (func_get_args() as $akey => $arg) {
        print_r($arg);
        echo PHP_EOL;
    }

    echo "</pre>";
}
