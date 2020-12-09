<?php

/**
 * Daten übertragen vom social network 1.0 zu 2.0
 * UNBEDINGT LÖSCHEN NACH VERWENDUNG!!!! 
 */

// Fehleranzeige 
error_reporting ( -1 );
ini_set ( 'display_errors', true ); 

define("IN_MYBB", 1);

require("global.php");
	global $db, $mybb, $user; 
  	$this_user = intval($mybb->user['uid']);
	  //TO DO: USER // BEWERBER
    $opt_blShow_user=intval($mybb->settings['blacklistAlert_show_user']);
  	$opt_blShow_guest=intval($mybb->settings['blacklistAlert_show_guest']);
//echo $opt_blShow_user;
if ($mybb->usergroup['canmodcp'] == 1)  {
//Einstellungen holen
 $this_user = intval($mybb->user['uid']); //wer ist online

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
echo "<div style=\"width:75%; margin:auto auto;padding:10px;\"> Test: Dein auto_increment von der Tabelle mit Posts ist: ".$lastIdPost.". Antwort ist 1? 1 ist gut! <br/>";
echo $usersCnt." User sind eingetragen. 0 wäre super ;)</div>";
if ($lastIdPost > 1 XOR $usersCnt > 0) {
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
    </div>" ;

    if($mybb->input['send']) {
        echo "<div style=\"width:75%; margin:auto auto;padding:10px;\">";
        $db->write_query("INSERT INTO `mybb_sn_posts`
        (sn_post_id, sn_pageid, sn_uid, sn_date, sn_social_post,sn_del_name) 
        SELECT social_id, userpageid, uid, social_date, social_post, del_username FROM `mybb_socialpost`
       ");
       echo "Posts übertragen<br/>";
        $db->write_query("INSERT INTO `mybb_sn_answers`
        (sn_aid, sn_post_id, sn_uid, sn_date, sn_answer, sn_del_name) 
        SELECT social_aid, social_id, social_uid, social_date, antwort, del_username FROM `mybb_socialantwort`
       ");
       echo "Antworten übertragen<br/>";
        $db->write_query("INSERT INTO `mybb_sn_friends`
        (sn_friendsid, sn_uid, sn_friendwith, sn_accepted) 
       SELECT lid, uid, isfriend, accepted FROM `mybb_socialfriends` WHERE uid in (SELECT uid FROM mybb_users) AND isfriend in (SELECT uid FROM mybb_users)
       ORDER BY `mybb_socialfriends`.`uid` ");
       echo "Freunde übertragen<br/>";
        $db->write_query("INSERT INTO `mybb_sn_likes`
        (sn_like_id, sn_postid, sn_answerid, sn_uid) 
        SELECT id, postid, antwortid, uid  FROM `mybb_sociallikes`");
        echo "likes übertragen
        <h1>WHUP! PARTY! Jetzt die Datei löschen und social network 1.0 deinstallieren</h1></div>";
    }
    
}


} else {
	error_no_permission();
}

?>