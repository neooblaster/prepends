<?php
/**
 * echos.php
 *
 * Elle emet le ou les messages dans le stream php://output et arrete le script.
 * Elle utilise en plus un element HTML <pre> pour conserver la présentation.
 *
 * @author    Nicolas DUPRE
 * @release   21/09/2017
 * @version   1.0.0
 * @package   Prepend
 */
function echos ()
{
    echo "<pre>";

    foreach (func_get_args() as $akey => $arg) {
        echo $arg . PHP_EOL;
    }

    echo "</pre>";
    exit;
}
