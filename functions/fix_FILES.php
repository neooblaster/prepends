<?php
/**
 * fix_FILES.php
 *
 * Corrige la variable super-global $_FILES pour simplifier les traitements par bouclage classique.
 *
 * @author    Nicolas DUPRE
 * @release   22/09/2017
 * @version   1.1.0
 * @package   Prepend
 */

/**
 * Corrige la variable super-global $_FILES pour simplifier les traitements par bouclage classique.
 *
 * @param bool $smart Rend le traitement intélligent afin de ne traiter que les champs inputs multiple.
 * @return bool
 */
function fix_FILES ($smart = true)
{
    static $already_fixed = false;

    if (!$already_fixed) {
        /** Parcourir toutes les éventuelles clés de fichier présentes dans $_FILES */
        foreach ($_FILES as &$data) {
            /** Si le mode smart est activé, ne pas traier les entrées n'ayant qu'un seul fichier */
            if($smart && !is_array($data['name'])) continue;

            /** Regroupement par index */
            $fixed = [];

            /** Cas "Multiple" */
            if (is_array($data['name'])) {
                foreach ($data['name'] as $index => $value){
                    $fixed[] = [
                        'name' => $data['name'][$index],
                        'type' => $data['type'][$index],
                        'tmp_name' => $data['tmp_name'][$index],
                        'error' => $data['error'][$index],
                        'size' => $data['size'][$index]
                    ];
                }
            }
            /** Cas normal pour $smart = false (autrement dis ALL) */
            else {
                $fixed[] = $data;
            }

            $data = $fixed;
        }

        unset($data);
    }

    $already_fixed = true;

    return true;
}
