<?php
define("IN_MYBB", 1);
require("global.php");
error_reporting(-1);
ini_set('display_errors', 1);

global $db, $mybb, $lang;
//load admin Languagepack
$lang->load("admin/socialnetwork");
//load languagepack
$lang->load("socialnetwork");
echo (
  '<style type="text/css">
body {
  background-color: #efefef;
  text-align: center;
  margin: 40px 100px;
  font-family: Verdana;
}
fieldset {
  width: 50%;
  margin: auto;
  margin-bottom: 20px;
}
legend {
  font-weight: bold;
}
</style>'
);
$gid = $db->fetch_field($db->write_query("SELECT gid FROM `" . TABLE_PREFIX . "settings` WHERE name like 'socialnetwork%' LIMIT 1;"), "gid");
require_once "inc/plugins/social/socialnetwork_temp_and_style.php";
if ($mybb->usergroup['canmodcp'] == 1) {
  echo "<h1>Update Script für Social Network Plugin Januar24</h1>";
  echo "<p>Updatescript wurde zuletzt am 12.01.24 aktualisiert</p>";

  echo "<p>Das Skript muss nur ausgeführt werden, wenn von einer alten auf eine neue Version geupdatet wird.<br> Bei Neuinstallation, muss hier nichts getan werden!</p>";

  echo '<form action="" method="post">';
  echo '<input type="submit" name="update" value="Update durchführen">';
  echo '</form>';

  if (isset($_POST['update'])) {

    socialnetwork_add_settings("update");
    rebuild_settings();
  }

  echo "<h1>Templates hinzufügen?</h1>";
  echo "<p>Mit diesem Klick werden templates hinzugefügt, die vorher gefehlt haben.</p>";
  echo '<form action="" method="post">';
  echo '<input type="submit" name="templates" value="templates hinzufügen">';
  echo '</form>';
  if (isset($_POST['templates'])) {
    socialnetwork_addtemplates();
    echo "<b>Templates hinzugefügt</b><br>";
  }
  echo "<h1>CSS Nachträglich hinzufügen?</h1>";
  echo "<p>Nach einem MyBB Upgrade fehlen die Stylesheets? <br> Hier kannst du den Standard Stylesheet neu hinzufügen.</p>";
  echo '<form action="" method="post">';
  echo '<input type="submit" name="css" value="css hinzufügen">';
  echo '</form>';
  $themesids = $db->write_query("SELECT tid FROM `" . TABLE_PREFIX . "themes`");
  $check_tid = $db->simple_select("themestylesheets", "*", "tid = '1' AND name = 'socialnetwork.css'");
  if ($db->num_rows($check_tid) == 0) {
    echo '<p>Stylesheet fehlt</p>';
  } else {
    echo '<p>Stylesheet ist schon im Mastertheme vorhanden</p>';
  }
  if (isset($_POST['css'])) {
    //Stylesheets checken
    echo "<p>CSS wurde zu Masterstyle hinzufügen</p>";
    $check_tid = $db->simple_select("themestylesheets", "*", "tid = '1' AND name = 'socialnetwork.css'");

    if ($db->num_rows($check_tid) == 0) {

      socialnetwork_addstylesheets();
      echo ("<br><b>CSS zu Default Theme hinzugefügt</b>");
    }
  }

  echo '<div style="width:100%; background-color: rgb(121 123 123 / 50%); display: flex; position:fixed; bottom:0;right:0; height:50px; justify-content: center; align-items:center; gap:20px;">
<div> <a href="https://github.com/katjalennartz/socialnetwork_2.0" target="_blank">Github Rep</a></div>
<div> <b>Kontakt:</b> risuena (Discord)</div>
<div> <b>Support:</b>  <a href="https://storming-gates.de/showthread.php?tid=1009907">SG Thread</a> oder via Discord</div>
</div>';
} else {
  echo "<h1>Kein Zugriff</h1>";
}
