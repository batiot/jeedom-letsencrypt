<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class letsencrypt extends eqLogic {

    /*
    preSave
    preInsert
    postInsert
    postSave
    */

    public static function dependancy_info() {
        $return = array();
        $return['log'] = __CLASS__ . '_update';
        $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '_progress';
        $state = '';
        exec(system::getCmdSudo()."certbot certificates", $out, $ret);
        $certbotOut = print_r($out,true);
        log::add('letsencrypt', 'debug','dependancy_info '.$certbotOut);
        if (strpos($certbotOut, 'command not found') !== false) {
            $state = 'CertBot not installed';
        } else {
            //Check Externak IP
            $externalIP = file_get_contents("http://ipecho.net/plain");
            if($externalIP===false){
                $state = 'No internet access from jeedom';
            }else{
                $externalAddr =config::byKey('externalAddr');
                if(empty($externalAddr)|| filter_var($externalAddr, FILTER_VALIDATE_IP)){
                    if(!empty($externalIP)){
                        config::save('externalProtocol','https://');
                        config::save('externalAddr',$externalIP.'.xip.io');
                        config::save('externalPort','443');
                        config::save('webserver','apache','letsencrypt'); 
                        config::save('testcert','false','letsencrypt');
                        $state = 'ok';
                    }else{
                        log::add('letsencrypt', 'error','WAN ip can not be found, force manually a valid resolvable hostname in the console admin/netwok/external panel');
                        $state = 'Internet Ip not found'; 
                    }
                }else{
                    config::save('externalProtocol','https://');
                    config::save('externalPort','443');
                    config::save('webserver','apache','letsencrypt');
                    config::save('testcert','false','letsencrypt');
                    $state = 'ok';  
                }
            }
        }
        $return['state'] = $state;
        return $return;
    }

    public static function dependancy_install() {
        //if (file_exists(jeedom::getTmpFolder(__CLASS__) . '_progress')) {
        //    return;
        //}
        log::remove(__CLASS__ . '_update');
        $cmd = dirname(__FILE__) . '/../../3rparty/install.sh ';
        $cmd .= ' ' . jeedom::getTmpFolder(__CLASS__) . '_progress';
        log::add('letsencrypt', 'debug','dependancy_install $cmd '. $cmd);
        return array('script' => $cmd, 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }


//    $cmd = 'sudo /bin/bash ' . dirname(__FILE__) . '/../../ressources/install.sh';
//    $cmd .= ' >> ' . log::getPathToLog('teleinfo_update') . ' 2>&1 &';
//    exec($cmd);



    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */


    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */

     


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom*/
    public static function cronDayly() {
        log::add('letsencrypt', 'debug','cronDayly');
        foreach (eqLogic::byType('letsencrypt') as $letsencrypt) {
            if ($letsencrypt->getIsEnable() == 1){
                $letsencrypt->renew();
            }
        }
    }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        log::add('letsencrypt', 'debug','preInsert');  
    }

    public function postInsert() {
        log::add('letsencrypt', 'debug','postInsert'); 
    }

    public function preSave() {
        log::add('letsencrypt', 'debug','preSave');
        if (empty(config::byKey('externalAddr'))) {
            throw new Exception(__('ExternalAddr ne peut pas être vide',__FILE__));
        }
        if (empty(config::byKey('email', 'letsencrypt'))) {
            throw new Exception(__('L\'email admin ne peut etre vide',__FILE__));
        }
    }
    
    public function fetchCertificate(){
        $hostname =config::byKey('externalAddr');
        $email =config::byKey('email', 'letsencrypt');
        $webserver =config::byKey('webserver', 'letsencrypt');
        $testcert =config::byKey('testcert', 'letsencrypt');

        if (empty($hostname)) {
            throw new Exception(__('ExternalAddr ne peut pas être vide',__FILE__));
        }
        if (empty($email)) {
            throw new Exception(__('L\'email admin ne peut etre vide',__FILE__));
        }
        if (empty($webserver)) {
            $webserver='apache';
        }
        $testServer="";
        if (!empty($testcert) && $testcert==='true') {
            $testServer = "--test-cert";    
        }

        log::add('letsencrypt', 'debug','fetchCertificate $hostname:'.$hostname.'   $email:'.$email);
        //Example
        //create or renew
            //sudo certbot --apache --agree-tos --force-renewal --noninteractive --test-cert --domain X.X.X.X.xip.io --email $email
        //Add additional domain
            //sudo certbot --apache --agree-tos --expand --noninteractive --test-cert --domain X.X.X.X.xip.io,X.X.X.X.nip.io --email $email
        //remove additional domain
            //sudo certbot --apache --agree-tos --cert-name X.X.X.X..xip.io --noninteractive --test-cert --domain X.X.X.X..xip.io --email $email
        exec(escapeshellcmd(system::getCmdSudo()."certbot --".$webserver." --force-renewal --agree-tos --force-renewal --noninteractive ".$testServer." --domain ".$hostname." --email ".$email), $out, $ret);
        $certbotOut = print_r($out,true);
        exec(system::getCmdSudo()."certbot certificates", $out, $ret);
        $certbotOut = print_r($out,true);
        $pattern="/Domains:\s(.*)/";
        $success = preg_match($pattern,$certbotOut , $match);
        if ($success) {
            $domain = $match[1];
            config::save('domain', $domain,'letsencrypt');
        }else{
            config::save('domain', '','letsencrypt');
        }
        $pattern="/Expiry Date:\s(.*)/";
        $success = preg_match($pattern, $certbotOut, $match);
        if ($success) {
            $expiry = $match[1];
            config::save('expiry', $expiry,'letsencrypt');
        }else{
            config::save('expiry', '','letsencrypt');
        }
        if ($ret!=0){
            log::add('letsencrypt', 'error','Certbot certificates failed'.$certbotOut);
            throw new Exception('Certbot certificates failed'. $certbotOut);
        }
    }

    public function renew(){
        //certbot renew
        try {
            exec(system::getCmdSudo() . "certbot renew", $out, $ret);
            log::add('letsencrypt', 'debug','Certbot renew '. print_r($out,true));
        } catch (Exception $exc) {
            log::add('letsencrypt', 'error','Certbot renew exception'. $exc->getMessage());
        }
    }


    public function clean(){
        exec(system::getCmdSudo()."certbot certificates", $out, $ret);
        $certbotOut = print_r($out,true);
        log::add('letsencrypt', 'debug','revoke_step1 '.$certbotOut );
        $pattern="/Domains:\s(.*)/";
        $success = preg_match($pattern, $certbotOut, $match);
        if ($success) {
            $CertName = $match[1];
            $testServer="";
            if (strrpos( $certbotOut, "TEST_CERT") !==false) { //ex 2018-02-03 19:16:01+00:00 (INVALID: TEST_CERT)
                $testServer = "--test-cert";    
            }
            log::add('letsencrypt', 'debug','revoke_step2 '.$CertName);
            exec(system::getCmdSudo() . "certbot revoke ".$testServer." --cert-path /etc/letsencrypt/live/".$CertName."/cert.pem", $out, $ret);
            log::add('letsencrypt', 'debug','revoke_step3 '.print_r($out,true));
            exec(system::getCmdSudo() . "certbot delete ".$testServer." --cert-name  ".$CertName, $out, $ret);
            log::add('letsencrypt', 'debug','revoke_step4 '.print_r($out,true));
            //exec(system::getCmdSudo() . "a2dissite 000-default-le-ssl.conf  ", $out, $ret);
            //log::add('letsencrypt', 'debug','revoke_step4 '.print_r($out,true));
            exec(system::getCmdSudo() . "systemctl reload apache2.service", $out, $ret);
            //log::add('letsencrypt', 'debug','remove_step5 '.print_r($out,true));
            //exec(dirname(__FILE__) . "/../../3rparty/nohup sh -c 'systemctl stop apache2.service && systemctl start apache2.service' &> remove.log", $out, $ret);
        }


    }

    public function postSave() {
        $isEnable =config::byKey('isEnable', 'letsencrypt');
        log::add('letsencrypt', 'debug','postSave'.$isEnable);
        if($this->getIsEnable()){
            $this->fetchCertificate();
            //$this->cronDayly($this->getId());
        }
    }
/*
    public function preUpdate() {
      log::add('letsencrypt', 'debug','preUpdate');
    }

    public function postUpdate() {
        log::add('letsencrypt', 'debug','postUpdate');   
    }

    public function preRemove() {
        log::add('letsencrypt', 'debug','postUpdate');    
    }
*/
    public function postRemove() {
        log::add('letsencrypt', 'debug','postUpdate');
        $this->clean();
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclancher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclancher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class letsencryptCmd extends cmd {
    /*     * *************************Attributs****************************** */
	public static $_widgetPossibility = array('custom' => true);    

    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
    */
    public function execute($_options = array()) {
        log::add('letsencrypt', 'debug','cmd execute '.print_r($_options,true));
        //if ($this->getType() == 'info') {
		//	return;
		//}
        //$eqLogic = $this->getEqLogic();
        
		//if ($this->getLogicalId() == 'refresh') {
		//	$this->getEqLogic()->updateWeatherData();
		//}
		//return false;


    }


    /*     * **********************Getteur Setteur*************************** */
}