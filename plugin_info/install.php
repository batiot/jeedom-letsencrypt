<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

//activation plugin
//function letsencrypt_install() {
//    return "letsencrypt_install";
//}

//function letsencrypt_update() {
//    return "letsencrypt_update_end";
//}
//desactivation plugin
function letsencrypt_remove() {

    foreach (eqLogic::byType('letsencrypt') as $letsencrypt) {
        log::add('letsencrypt', 'debug','letsencrypt_install_remove');
        $letsencrypt->clean();
    }
}
?>