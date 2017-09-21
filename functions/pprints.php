<?php
/**
 * pprints.php
 *
 * Affiche le ou les tableaux le stream php://output de manière structurée à l'aide de la fonction "pprint" et
 * met fin au script.
 *
 * @author    Nicolas DUPRE
 * @release   21/09/2017
 * @version   1.0.0
 * @package   Prepend
 */
function pprints()
{
    pprint(func_get_args());
    exit;
}
