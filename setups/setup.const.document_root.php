<?php
/**
 * setup.const.document_root.php
 *
 * Définition de constante "Magique" à partir de la variable super-globale $_SERVER
 *
 * @author    Nicolas DUPRE
 * @release   22/09/2017
 * @version   1.0.0
 * @package   Prepend
 */

define('__ROOT__', $_SERVER['DOCUMENT_ROOT']);
define('__RURI__', $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']);
