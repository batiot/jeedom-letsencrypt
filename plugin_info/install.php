<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

//activation plugin
function letsencrypt_install() {
    return "letsencrypt_install";
}

function letsencrypt_update() {

    //Example
    //create or renew
        //sudo certbot --apache --agree-tos --force-renewal --noninteractive --test-cert --domain X.X.X.X.xip.io --email $email
    //Add additional domain
        //sudo certbot --apache --agree-tos --expand --noninteractive --test-cert --domain X.X.X.X.xip.io,X.X.X.X.nip.io --email $email
    //remove additional domain
            //sudo certbot --apache --agree-tos --cert-name X.X.X.X..xip.io --noninteractive --test-cert --domain X.X.X.X..xip.io --email $email


    return "letsencrypt_update_end";
}
//desactivation plugin
function letsencrypt_remove() { 

    exec(system::getCmdSudo()."certbot certificates", $out, $ret);
    log::add('letsencrypt', 'debug','remove_step1 '. print_r($out,true));
    $pattern='\Domains:\s(.*)';
    $success = preg_match($pattern, print_r($out,true), $match);

    if ($success) {
        $CertName = $match[1];
        log::add('letsencrypt', 'debug','remove_step2 '.$CertName);
        /*Clean
        sudo certbot revoke --test-cert --cert-path /etc/letsencrypt/live/109.26.40.107.xip.io/cert.pem
        sudo certbot delete --test-cert --cert-name  109.26.40.107.xip.io
        sudo a2dissite 000-default-le-ssl.conf
        sudo systemctl reload apache2
        //sudo systemctl start apache2.service
        */
        exec(system::getCmdSudo() . "certbot revoke --test-cert --cert-path /etc/letsencrypt/live/".$CertName."/cert.pem", $out, $ret);
        log::add('letsencrypt', 'debug','remove_step3 '.print_r($out,true));
        exec(system::getCmdSudo() . "certbot delete --test-cert --cert-name  ".$CertName, $out, $ret);
        log::add('letsencrypt', 'debug','remove_step3 '.print_r($out,true));
        exec(system::getCmdSudo() . "systemctl reload apache2.service", $out, $ret);
        log::add('letsencrypt', 'debug','remove_step4 '.print_r($out,true));
        //exec(dirname(__FILE__) . "/../../3rparty/nohup sh -c 'systemctl stop apache2.service && systemctl start apache2.service' &> remove.log", $out, $ret);
        //log::add('letsencrypt', 'debug','remove_step4 see log in ./plugins/3rparty/remove.log');
    }
    
    return "letsencrypt_remove_end";
}
?>