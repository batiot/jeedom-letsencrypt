<?php
/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class letsencrypt extends eqLogic {

    public static function dependancy_info() {
        $return = array();
        $return['log'] = __CLASS__ . '_update';
        $return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '_progress';
        $state = '';
        exec(system::getCmdSudo()."certbot certificates", $out, $ret);
        log::add('letsencrypt', 'debug ','dependancy_info '.print_r($out,true));
        if (strpos(print_r($out,true), 'command not found') !== false) {
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
                        $state = 'ok';
                    }else{
                        log::add('letsencrypt', 'error','WAN ip can not be found, force manually a valid resolvable hostname in the console admin/netwok/external panel');
                        $state = 'Internet Ip not found'; 
                    }
                }else{
                    config::save('externalProtocol','https://');
                    config::save('externalPort','443');
                    $state = 'ok';  
                }
            }
        }
        $return['state'] = $state;
        return $return;
    }

    public static function dependancy_install() {
        log::remove(__CLASS__ . '_update');
        $cmd = dirname(__FILE__) . '/../../3rparty/install.sh ';
        $cmd .= ' ' . jeedom::getTmpFolder(__CLASS__) . '_progress';
        log::add('letsencrypt', 'debug','dependancy_install $cmd '. $cmd);
        return array('script' => $cmd, 'log' => log::getPathToLog(__CLASS__ . '_update'));
    }


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
        log::add('letsencrypt', 'debug ','cronDayly');
            //certbot renew
        try {
            exec(system::getCmdSudo() . "certbot renew", $out, $ret);
            log::add('letsencrypt', 'debug','Certificat renew '. print_r($out,true));
        } catch (Exception $exc) {
            log::add('letsencrypt', 'error','Certificat renew exception'. $exc->getMessage());
        }
      }

    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        log::add('letsencrypt', 'debug ','preInsert');  
    }

    public function postInsert() {
        log::add('letsencrypt', 'debug ','postInsert'); 
    }

    public function preSave() {
        log::add('letsencrypt', 'debug ','preSave');
    }
    
    private function getCertificate(){
        $hostname =config::byKey('externalAddr');
        $email =config::byKey('email', 'letsencrypt');

        if (empty($hostname)) {
            throw new Exception(__('ExternalAddr ne peut pas être vide',__FILE__));
        }
        if (empty($email)) {
            throw new Exception(__('L\'email admin ne peut etre vide',__FILE__));
        }

        log::add('letsencrypt', 'debug ','preSave $hostname'.$hostname.'   $email'.$email);
        
        exec(escapeshellcmd(system::getCmdSudo()."certbot --apache --force-renewal --agree-tos --force-renewal --noninteractive --test-cert --domain ".$hostname." --email ".$email), $out, $ret);
        $certbotOut = print_r($out,true);
        if ($ret!=0){
            log::add('letsencrypt', 'error','Certbot apache failed'.  $certbotOut);
            throw new Exception('letsencrypt certbot apache failed'. $certbotOut);
        }

        exec(system::getCmdSudo()."certbot certificates", $out, $ret);
        $certbotOut = print_r($out,true);
        $pattern="/Domains:\s(.*)/";
        $success = preg_match($pattern,$certbotOut , $match);
        if ($success) {
            $domain = $match[1];
            config::save('domain', $domain,'letsencrypt');
        }
        $pattern="/Expiry Date:\s(.*)/";
        $success = preg_match($pattern, $certbotOut, $match);
        if ($success) {
            $expiry = $match[1];
            config::save('expiry', $expiry,'letsencrypt');
        }
        if ($ret!=0){
            //log::add('letsencrypt', 'error','Certbot certificates failed'.print_r($out,true));
            throw new Exception('Certbot certificates failed'. $certbotOut);
        }
        //if(strrpos(print_r($out,true), "Domains: ".$hostname) === false){
        //    log::add('letsencrypt', 'error','Certificates not valid '.print_r($out,true));
            //throw new Exception('Certificates not valid'. print_r($out,true));
        //}
        /*
        if ($this->getConfiguration('mode') == 'fixe' || $this->getConfiguration('mode') == 'dynamic') {
            $this->setSubType('string');
        } else {
            $this->setSubType('numeric');
            if ($this->getConfiguration('mode') == 'fixe') {
                $this->setUnite('min');
            } else {
                $this->setUnite('Km');
            }
            //$this->setDependency();
        }
        */
    }

    public function postSave() {
        log::add('letsencrypt', 'debug ','postSave');
    }

    public function preUpdate() {
      log::add('letsencrypt', 'debug ','preUpdate');
      if (empty(config::byKey('externalAddr'))) {
        throw new Exception(__('ExternalAddr ne peut pas être vide',__FILE__));
      }

      if (empty($this->getConfiguration('email'))) {
        throw new Exception(__('L\'email admin ne peut etre vide',__FILE__));
      }
    }



    public function postUpdate() {
        log::add('letsencrypt', 'debug ','postUpdate');       
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
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


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
    */
    public function execute($_options = array()) {
        log::add('letsencrypt', 'debug ','cmd execute '.print_r($_options,true));
    }

    /*     * **********************Getteur Setteur*************************** */
}