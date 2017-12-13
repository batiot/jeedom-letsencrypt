<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function letsencrypt_install() {
    $random_minutes = rand(1, 59);
    $crontab_schedule = $random_minutes." 1 * * *";
    $cron = cron::byClassAndFunction('letsencrypt', 'renew_letsencrypt');
    if (!is_object($cron)) {
      $cron = new cron();
      $cron->setClass('letsencrypt');
      $cron->setFunction('renew_letsencrypt');
      $cron->setEnable(1);
      $cron->setDeamon(0);
      $cron->setSchedule($crontab_schedule);
      $cron->save();
    }
}
  
function letsencrypt_update() {
    $random_minutes = rand(1, 59);
    $crontab_schedule = $random_minutes." 1 * * *";
    $cron = cron::byClassAndFunction('letsencrypt', 'renew_letsencrypt');
    if (!is_object($cron)) {
      $cron = new cron();
      $cron->setClass('letsencrypt');
      $cron->setFunction('renew_letsencrypt');
      $cron->setEnable(1);
      $cron->setDeamon(0);
      $cron->setSchedule($crontab_schedule);
      $cron->save();
    }
    $cron->setSchedule($crontab_schedule);
    $cron->save();
    $cron->stop();
}
  
function letsencrypt_remove() {
   $cron = cron::byClassAndFunction('letsencrypt', 'renew_letsencrypt');
   if (is_object($cron)) {
     $cron->stop();
     $cron->remove();
   }
}

?>