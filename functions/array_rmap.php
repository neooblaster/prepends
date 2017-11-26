<?php
/**
 * array_rmap.php
 *
 * %DESC BLOCK%
 *
 * @author    Nicolas DUPRE
 * @release   17/10/2017
 * @version   1.0.0
 * @package   Index
 */
function array_rmap($callback, &$arr){
    $tmp = array_map($callback, $arr);

    $arr = $tmp;
}
