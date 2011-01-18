<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <!-- <link href="gfx/favicon.png" rel="shortcut icon" /> -->
    <?php echo style('phpsysinfo/nextgen.css'); ?>
    <?php echo style('nyroModal.full.css'); ?>
    <?php echo style('jquery.jgrowl.css'); ?>
    <?php echo style('jquery.dataTables.css'); ?>
    <?php echo style('jquery.treeTable.css'); ?>

    <?php echo script('jquery.js'); ?>
    <?php echo script('plugins/jquery.dataTables.js'); ?>
    <?php echo script('plugins/jquery.nyroModal.js'); ?>
    <?php echo script('plugins/jquery.jgrowl.js'); ?>
    <?php echo script('plugins/jquery.timers.js'); ?>
    <?php echo script('plugins/jquery.treeTable.js'); ?>
    <?php echo phpscript('phpsysinfo.js/'.$id); ?>

    <title>ProteoERP monitoreo de sistema</title>
  </head>
  <body>
    <div id="loader">

      <h1>Cargando... por favor espere!</h1>
    </div>
    <div id="errors" style="display: none; width: 940px">
      <div id="errorlist">
        <h2>Disculpe. Algo salio mal.</h2>
      </div>
    </div>
    <div id="container" style="display: none;">
      <h1>
        <a href="#errors" class="nyroModal">
          <img id="warn" style="vertical-align: middle; display:none; border:0px;" src="<?php echo ASSETS_URL.THEME_PATH.IMAGES_PATH; ?>gfx/attention.png" alt="warning" />
        </a>
        <span id="title">
          <span id="lang_001">Informaci&oacute;n del sitema</span>
          :&nbsp;<span id="s_hostname_title"></span>
          (<span id="s_ip_title"></span>)
        </span>

      </h1>
      <div id="vitals">
        <h2><span id="lang_002">System vitals</span></h2>
        <table class="stripeMe" id="vitalsTable" cellspacing="0">
          <tr>
            <td style="width:160px;"><span id="lang_003">Hostname</span></td>
            <td><span id="s_hostname"></span></td>

          </tr>
          <tr>
            <td style="width:160px;"><span id="lang_004">Listening IP</span></td>
            <td><span id="s_ip"></span></td>
          </tr>
          <tr>
            <td style="width:160px;"><span id="lang_005">Kernel Version</span></td>
            <td><span id="s_kernel"></span></td>

          </tr>
          <tr>
            <td style="width:160px;"><span id="lang_006">Distro Name</span></td>
            <td><span id="s_distro"></span></td>
          </tr>
          <tr>
            <td style="width:160px;"><span id="lang_007">Uptime</span></td>
            <td><span id="s_uptime"></span></td>

          </tr>
          <tr>
            <td style="width:160px;"><span id="lang_008">Current Users</span></td>
            <td><span id="s_users"></span></td>
          </tr>
          <tr>
            <td style="width:160px;"><span id="lang_009">Load Averages</span></td>
            <td id="s_loadavg"></td>

          </tr>
        </table>
      </div>
      <div id="hardware">
      </div>
      <div id="memory">
      </div>
      <div id="filesystem">
      </div>

      <div id="network">
        <h2><span id="lang_021">Network Usage</span></h2>
        <table class="stripeMe" id="networkTable" cellspacing="0">
          <thead>
            <tr>
              <th><span id="lang_022">Interface</span></th>
              <th class="right" style="width:60px;"><span id="lang_023">Recieved</span></th>

              <th class="right" style="width:60px;"><span id="lang_024">Transfered</span></th>
              <th class="right" style="width:60px;"><span id="lang_025">Error/Drops</span></th>
            </tr>
          </thead>
          <tbody id="tbody_network">
          </tbody>
        </table>
      </div>

      <div id="voltage" style="display: none;">
        <h2><span id="lang_052">Voltage</span></h2>
        <table class="stripeMe" id="voltageTable" cellspacing="0">
          <thead>
            <tr>
              <th><span id="lang_059">Label</span></th>
              <th class="right"><span id="lang_052">Voltage</span></th>

              <th class="right" style="width: 80px;"><span id="lang_055">Min</span></th>
              <th class="right" style="width: 80px;"><span id="lang_056">Max</span></th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>

      <div id="temp" style="display: none;">
        <h2><span id="lang_051">Temperature</span></h2>
        <table class="stripeMe" id="tempTable" cellspacing="0">
          <thead>
            <tr>
              <th><span id="lang_059">Label</span></th>
              <th class="right" style="width: 80px;"><span id="lang_054">Value</span></th>

              <th class="right" style="width: 80px;"><span id="lang_058">Limit</span></th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div id="fan" style="display: none;">

        <h2><span id="lang_053">Fan</span></h2>
        <table class="stripeMe" id="fanTable" cellspacing="0">
          <thead>
            <tr>
              <th><span id="lang_059">Label</span></th>
              <th class="right" style="width: 80px;"><span id="lang_054">Value</span></th>
              <th class="right" style="width: 80px;"><span id="lang_055">Min</span></th>

            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div id="ups" style="display: none;">
      </div>
      <div id="footer">
      </div>
    </div>
    <center><b><?php echo $regresar; ?></b></center>
  </body>
</html>