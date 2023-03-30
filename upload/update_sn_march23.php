<?php
define("IN_MYBB", 1);
require("global.php");
// error_reporting(E_ERROR | E_PARSE);
// ini_set('display_errors', true);

global $db, $mybb, $lang;

if (!$mybb->settings['socialnetwork_mentionsownpage']) {
  $setting_array = array(
    'socialnetwork_mentionsownpage' => array(
      'title' => $lang->socialnetwork_settings_mentionsownpage_tit,
      'description' => $lang->socialnetwork_settings_mentionsownpage,
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 18
    )
  );
  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $setting['gid'] = $gid;
    $db->insert_query('settings', $setting);
  }
  rebuild_settings();
  echo "Settings hinzugefügt: Mentions auf eigener Seite anzeigen, ja oder nein.<br>";
}
if (!$db->field_exists("sn_page_id", "sn_answers")) {
  $db->add_column("sn_answers", "sn_page_id", "int(10) NOT NULL DEFAULT 0");
  echo "sn_page_id zu tabelle sn_answers hinzugefügt. <br>Datei jetzt löschen!<br>";
}

if ($db->field_exists("sn_page_id", "sn_answers")) {
  echo "Das Feld sn_page_id in der tabelle 'prefix'_sn_answers existiert schon, bitte die Datei löschen<br>";
}

if ($mybb->settings['socialnetwork_mentionsownpage']) {
  echo "Die Einstellung Mentions auf eigener Seite anzeigen, ja oder nein existiert schon. bitte datei löschen<br>";
}

$template = array();

$template[0] = array(
  "title" => 'socialnetwork_member_shortinfos',
  "template" => '<div class="socialprofile">
    <div class="sociallogo">
        {$logo}
    </div>
    <div class="sn_memInfo">
      <div class="sninfowrap--img">
        <img src="{$sn_thispage[\\\'sn_avatar\\\']}" class="imghighlight sn-profilbild" alt="profilbild" />
      </div>
      <div class="sninfowrap">
      <div class="socialcard">
        <h2 class="heading2">{$sn_thispage[\\\'sn_nickname\\\']}</h2>
        {$socialnetwork_member_infobit}
      </div>
      <div class="socialcard">
        <div class="sninfos__item answertit heading3">{$sn_thispage[\\\'sn_nickname\\\']}\\\'s friends</div>
  
        {$socialnetwork_member_friendsbit}
      </div>
      <div class="socialcard sninfos">
        <div class="sninfos__item answertit heading3">last post on {$sn_thispage[\\\'sn_nickname\\\']}\\\'s page</div>
        <div class="sninfos__item poster heading4">{$lastpost[\\\'poster\\\']}</div>
        <div class="sninfos__item post">{$lastpost[\\\'post\\\']}</div>
        <div class="sninfos__item date">{$lastpost[\\\'sndate\\\']}</div>
      </div>
        <div class="socialcard">
        <div class="sninfos__item answertit heading3">last answer on {$sn_thispage[\\\'sn_nickname\\\']}\\\'s page</div>
        <div class="sninfos__item poster heading4">{$lastanswer[\\\'poster\\\']}</div>
        <div class="sninfos__item post">{$lastanswer[\\\'post\\\']}</div>
        <div class="sninfos__item date">{$lastanswer[\\\'sndate\\\']} </div>
      </div>
          <div class="socialcard sninfos">
        <div class="sninfos__item answertit heading3">last post from {$sn_thispage[\\\'sn_nickname\\\']}</div>
        <div class="sninfos__item poster heading4">{$lastpostthis[\\\'poster\\\']}</div>
        <div class="sninfos__item post">{$lastpostthis[\\\'post\\\']}</div>
        <div class="sninfos__item date">{$lastpostthis[\\\'sndate\\\']}</div>
      </div>
        <div class="socialcard">
        <div class="sninfos__item answertit heading3">last answer from {$sn_thispage[\\\'sn_nickname\\\']}</div>
        <div class="sninfos__item poster heading4">{$lastanswerthis[\\\'poster\\\']}</div>
        <div class="sninfos__item post">{$lastanswerthis[\\\'post\\\']}</div>
        <div class="sninfos__item date">{$lastanswerthis[\\\'sndate\\\']} </div>
      </div>
      </div>
        <div class="sociallink">
           <h2 class="heading2"><a href="{$url}/member.php?action=profile&uid={$userspageid}&area=socialnetwork">Zu {$sn_thispage[\\\'sn_nickname\\\']}\\\'s Heartstring</a></h2>
    </div>
     </div>
  
  </div>',
  "sid" => "-2",
  "version" => "1.0",
  "dateline" => TIME_NOW
);
$template[1] = array(
  "title" => 'socialnetwork_member_shortinfos_nopage',
  "template" => '<div class="socialprofile">
    {$logo}<br>
    {$memprofile[\\\'username\\\']} besitzt kein Social Network.
  </div>
    ',
  "sid" => "-2",
  "version" => "1.0",
  "dateline" => TIME_NOW
);

$test = $db->simple_select("templates", "title", "title LIKE '%socialnetwork_member_shortinfos_nopage%'");
if ($db->num_rows($test) == 0) {
  foreach ($template as $row) {
    echo $row['title']." wurde hinzugefügt <br>";
    $db->insert_query("templates", $row);
  }
  echo "<b>Templates hinzugefügt</b>";
} else {
  echo "Templates existieren schon oder Fehler";
}
