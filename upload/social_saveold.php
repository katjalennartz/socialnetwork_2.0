<?php

/**
 * Daten übertragen vom social network 1.0 zu 2.0
 * UNBEDINGT LÖSCHEN NACH VERWENDUNG!!!! 
 */

// Fehleranzeige 
// error_reporting(-1);
// ini_set('display_errors', true);

define("IN_MYBB", 1);
include (MYBB_ROOT."inc/plugins/socialnetwork.php");
require("global.php");
global $db, $mybb, $user;
$this_user = intval($mybb->user['uid']);
//TO DO: USER // BEWERBER
$opt_blShow_user = intval($mybb->settings['blacklistAlert_show_user']);
$opt_blShow_guest = intval($mybb->settings['blacklistAlert_show_guest']);
//echo $opt_blShow_user;
if ($mybb->usergroup['canmodcp'] == 1) {
  //Einstellungen holen
  $this_user = intval($mybb->user['uid']); //wer ist online
  echo '<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Titel</title>
  </head>
  <body>';
  echo "<div style=\"width:75%; margin:auto auto;padding:10px;\"><h1>Daten übertragen Social Network</h1><br/>
Nicht sonderlich hübsch, aber funktional :) <br/>
1. Social Network 2.0 installieren <br/>
2. Das hier machen <br /><br />
<br>Wichtig:</br> Diese Datei <b>nach</b> der Verwendung löschen.
<br/>
<b>Wichtig 2:</b> Diese Datei benutzen <b>bevor</b> 
du irgendwelche Posts/Users/etc. anlegst oder das Social network benutzt. Ansonsten wird das Ganze nämlich nicht richtig funktionieren.<br/>
</div>";

  $databasename = $db->fetch_field($db->write_query("SELECT DATABASE()"), "DATABASE()");
  $lastIdPost = $db->fetch_field($db->write_query("
SELECT AUTO_INCREMENT FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = '" . $databasename . "' AND TABLE_NAME = '" . TABLE_PREFIX . "sn_posts'"), "AUTO_INCREMENT");

  $usersCntQ = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "sn_users");
  $usersCnt = $db->num_rows($usersCntQ);
  echo "<div style=\"width:75%; margin:auto auto;padding:10px;\"> Test: Dein auto_increment von der Tabelle mit Posts ist: " . $lastIdPost . ". Antwort ist 1? 1 ist gut! <br/>";
  echo $usersCnt . " User sind eingetragen. 0 wäre super ;)</div>";
  if ($lastIdPost > 1 || $usersCnt > 0) {
    echo "<div style=\"width:75%; margin:auto auto;padding:10px;\">
Du siehst den Button nicht? Glückwunsch, dann hast du Wichtig 2 nicht beachtet ;) 
In dem Fall, am besten im Support melden :D <br/>
</div>";
  } else {
    echo "<div style=\"width:75%; margin:auto auto;padding:10px;\">
    Super, dann können wir jetzt die Daten übertragen: <br/>
    <form method=\"post\" name=\"magic\" id=\"magic\"> 
    <input type=\"submit\" name=\"send\" value=\"do magic\">
    </form>
    </div>";

    if ($mybb->input['send']) {
      echo "<div style=\"width:75%; margin:auto auto;padding:10px;\">";
      $db->write_query("INSERT INTO `" . TABLE_PREFIX . "sn_posts`
        (sn_post_id, sn_pageid, sn_uid, sn_date, sn_social_post, sn_del_name) 
        SELECT social_id, userpageid, uid, social_date, social_post, del_username FROM `" . TABLE_PREFIX . "socialpost`
       ");
      echo "Posts übertragen<br/>";
      $db->write_query("INSERT INTO `" . TABLE_PREFIX . "sn_answers`
        (sn_aid, sn_post_id, sn_uid, sn_date, sn_answer, sn_del_name) 
        SELECT social_aid, social_id, social_uid, social_date, antwort, del_username FROM `" . TABLE_PREFIX . "socialantwort`
       ");
      echo "Antworten übertragen<br/>";
      $db->write_query("INSERT INTO `" . TABLE_PREFIX . "sn_friends`
       (sn_friendsid, sn_uid, sn_friendwith, sn_accepted) 
      SELECT lid, uid, isfriend, accepted FROM `" . TABLE_PREFIX . "socialfriends` WHERE uid in (SELECT uid FROM " . TABLE_PREFIX . "users) AND isfriend in (SELECT uid FROM " . TABLE_PREFIX . "users)
      ORDER BY `" . TABLE_PREFIX . "socialfriends`.`uid` ");
      echo "Freunde übertragen<br/>";

      $db->write_query("INSERT INTO `" . TABLE_PREFIX . "sn_users`
              (uid, sn_nickname, sn_avatar, sn_userheader, sn_alertPost, sn_alertFriend, sn_alertLike, sn_alertMention, sn_alertFriendReq) 
      SELECT uid, social_nutzername, social_profilbild, social_titelbild, social_postcheck, social_friendcheck, social_likecheck, social_namedcheck, 1  FROM `" . TABLE_PREFIX . "users` WHERE userpage !=''");
      echo "User übertragen<br/>";

      $db->write_query("UPDATE `" . TABLE_PREFIX . "sn_posts`  SET sn_del_name ='' WHERE EXISTS (SELECT uid FROM " . TABLE_PREFIX . "users WHERE uid = sn_uid) ");
      $db->write_query("UPDATE `" . TABLE_PREFIX . "sn_answers`  SET sn_del_name ='' WHERE EXISTS (SELECT uid FROM " . TABLE_PREFIX . "users WHERE uid = sn_uid) ");
      //where not exists (select t1.commonid from table1 t1 where t1.commonid = c.commonid)

      $db->write_query("INSERT INTO `" . TABLE_PREFIX . "sn_likes`
        (sn_like_id, sn_postid, sn_answerid, sn_uid) 
        SELECT id, postid, antwortid, uid  FROM `" . TABLE_PREFIX . "sociallikes`");
      echo "likes übertragen";


      echo "<h1>WHUP! PARTY! Jetzt die Datei löschen und social network 1.0 deinstallieren</h1></div>";
    }
echo "<div style=\"width:75%; margin:auto auto;padding:10px;\">
    Die neuen Templates wurden bei der Deinstallation von 1.0 mit gelöscht? <br>
    Dann einmal hier drücken und sie neu einfügen:<br>
    <form method=\"post\" name=\"templates\" id=\"templates\"> 
    <input type=\"submit\" name=\"sendTemp\" value=\"add templates again\">
    </form>
</div>" ;
if ($mybb->input['sendTemp']) { 
  $templateTest = $db->write_query("SELECT * FROM " . TABLE_PREFIX ."templates WHERE title like 'socialnetwork%'");
  if (mysqli_num_rows($templateTest) > 0){
    echo "Sicher das keine Templates da sind? Schau noch einmal nach. Du findest sie in jedem Style in der Gruppe 'Soziales Netzwerk.";
  } else {
    socialnetwork_addtemplates();
  }
} 

  }
} else {
  error_no_permission();
}
echo ('</body></html>');
