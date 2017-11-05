<?php

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
  <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Admin email}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" type="email" data-l1key="email" />
            </div>
        </div>
  </fieldset>
  <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{WebServer}}</label>
            <div class="col-lg-2">
            <?=config::byKey('webserver', 'letsencrypt')?>
            </div>
        </div>
  </fieldset>
  <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Test Certificate}}</label>
            <div class="col-lg-2">
            <?=config::byKey('testcert', 'letsencrypt')?>
            </div>
        </div>
  </fieldset>
</form>
<script>
  function letsencrypt_postSaveConfiguration(){
      /*
   $.ajax({
    type: "POST",
    url: "plugins/gcm/core/ajax/letsencrypt.ajax.php",
    data: {
      action: "genFirebaseMsgTmpl",
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });*/
  console.log('letsencrypt_postSaveConfiguration')
 }
</script>