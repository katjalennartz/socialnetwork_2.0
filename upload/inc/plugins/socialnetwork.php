<?php

/**
 * social network for mybb Plugin
 *
 * @author risuena
 * @version 2.0
 * @copyright risuena 2020
 * 
 */
// enable for Debugging:
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', true);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function socialnetwork_info()
{
    global $lang, $db, $plugins_cache;
    $lang->load("socialnetwork");

    $plugininfo = array(
        "name" => $lang->socialnetwork_title,
        "description" => $lang->socialnetwork_desc,
        "website" => "https://github.com/katjalennartz/socialnetwork_2.0",
        "author" => "risuena",
        "authorsite" => "https://lslv.de/risu",
        "version" => "2.0",
        "compatability" => "18*"
    );
    if (socialnetwork_is_installed() && is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active']['socialnetwork']) {
        $result = $db->simple_select('settinggroups', 'gid', "name = 'socialnetwork'");
        $set = $db->fetch_array($result);
        if (!empty($set)) {
            $desc = $plugininfo['description'];
            $plugininfo['description'] = "" . $desc . "<div style=\"float:right;\"><img src=\"styles/default/images/icons/custom.png\" alt=\"\" style=\"margin-left: 10px;\" />
                                                        <a href=\"index.php?module=tools-socialnetwork\" style=\"margin: 10px;\">" . $lang->socialnetwork_infoacp . "</a> | 
                                                        <img src=\"styles/default/images/icons/custom.png\" alt=\"\" style=\"margin-left: 10px;\" /><a href=\"index.php?module=config-settings&amp;action=change&amp;gid=" . (int)$set['gid'] . "\" style=\"margin: 10px;\">" . $lang->socialnetwork_infoolddata . "</a><hr style=\"margin-bottom: 5px;\"></div>";
        }
    }
    return $plugininfo;
}

function socialnetwork_is_installed()
{
    global $db;
    if ($db->table_exists("sn_users")) {
        return true;
    }
    return false;
}

function socialnetwork_install()
{
    global $db, $lang;
    $lang->load("socialnetwork");
    socialnetwork_uninstall();
    //create tables for userdata
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_users` (
        `uid` int(20) NOT NULL AUTO_INCREMENT,
        `sn_nickname` varchar(200) NOT NULL,
        `sn_avatar` varchar(200) NOT NULL,
        `sn_userheader` varchar(200) NOT NULL,
        `sn_alertPost` TINYINT(1) NOT NULL DEFAULT '1',
        `sn_alertFriend` TINYINT(1) NOT NULL DEFAULT '1',
        `sn_alertLike` TINYINT(1) NOT NULL DEFAULT '1',
        `sn_alertMention` TINYINT(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`uid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create table for posts
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_posts` (
		`sn_post_id` int(20) NOT NULL AUTO_INCREMENT,
        `sn_pageid` int(20) NOT NULL,
		`sn_uid` int(20) NOT NULL,
  		`sn_social_post` varchar(300) NOT NULL,
  		`sn_del_username` varchar(300) DEFAULT NUll,
  		`sn_del_nickname` varchar(300) DEFAULT NUll,
  		`sn_del_avatar` varchar(30) DEFAULT NUll,
	PRIMARY KEY (`sn_post_id`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create table for answers
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_answers` (
		`sn_aid` int(20) NOT NULL AUTO_INCREMENT,
		`sn_post_id` int(20) NOT NULL,
  		`sn_date` datetime NOT NULL,
  		`sn_uid` int(20) NOT NULL,
  		`sn_answer` varchar(300) NOT NULL,
  		`sn_del_username` varchar(300) DEFAULT NUll,
  		`sn_del_nickname` varchar(300) DEFAULT NUll,
  		`sn_del_avatar` varchar(30) DEFAULT NUll,
  	PRIMARY KEY (`sn_aid`)
	) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create table for friends
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_friends` (
        `sn_friendsid` int(20) NOT NULL AUTO_INCREMENT,
  	    `sn_uid` int(20) NOT NULL,
  	    `sn_friendwith` int(20) NOT NULL,
  	    `sn_accepted` int(1) NOT NULL DEFAULT 0,
  	PRIMARY KEY (`sn_friendsid`)
	) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create tables for likes.
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_likes` (
        `sn_like_id` int(11) NOT NULL AUTO_INCREMENT,
        `sn_postid` int(11) NOT NULL,
        `sn_answerid` int(11) NOT NULL,
        `sn_uid` int(11) NOT NULL,
        PRIMARY KEY (`sn_like_id`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");


    //create table for uploaded images.
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_imgs` (
        `sn_imgId` int(11) NOT NULL AUTO_INCREMENT,
        `sn_filesize` int(11) NOT NULL,
        `sn_filename` varchar(200) NOT NULL,
        `sn_width` int(200) NOT NULL,
        `sn_height` int(11) NOT NULL,
        `sn_uid` int(11) NOT NULL,
        `sn_postId` int(11) NOT NULL,
        PRIMARY KEY (`sn_imgId`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //einstellungen:
    $db->write_query("ALTER TABLE `" . TABLE_PREFIX . "usergroups` ADD `socialnetwork_isallowed` INT(1) NOT NULL DEFAULT '0', ADD `socialnetwork_canedit` INT(1) NOT NULL DEFAULT '0', ADD `socialnetwork_canmoderate` INT(1) NOT NULL DEFAULT '0';");
    $db->write_query('UPDATE ' . TABLE_PREFIX . 'usergroups SET socialnetwork_isallowed = 1 WHERE canusercp = 1');
    $db->write_query('UPDATE ' . TABLE_PREFIX . 'usergroups SET socialnetwork_canedit = 1, socialnetwork_canmoderate = 1 WHERE gid IN (2, 3, 4, 6)');

    $settings_group = array(
        "gid" => "",
        "name" => "socialnetwork",
        "title" => $lang->socialnetwork_settings_title,
        "description" => $lang->socialnetwork_settings_desc,
        "disporder" => "0",
        "isdefault" => "0",
    );

    $db->insert_query("settinggroups", $settings_group);
    $gid = $db->insert_id();

    $setting_array = array(
        'socialnetwork_html' => array(
            'title' =>  $lang->socialnetwork_settings_html_tit,
            'description' => $lang->socialnetwork_settings_html,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 1
        ),
        'socialnetwork_mybbcode' => array(
            'title' => $lang->socialnetwork_settings_mybbcode_tit,
            'description' => $lang->socialnetwork_settings_mybbcode,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 2
        ),
        'socialnetwork_img' => array(
            'title' => $lang->socialnetwork_settings_img_tit,
            'description' => $lang->socialnetwork_settings_img,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 3
        ),
        'socialnetwork_badwords' => array(
            'title' => $lang->socialnetwork_settings_badwords_tit,
            'description' => $lang->socialnetwork_settings_badwords,
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 4
        ),
        'socialnetwork_videos' => array(
            'title' => $lang->socialnetwork_settings_video_tit,
            'description' => $lang->socialnetwork_settings_video,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 5
        ),
        'socialnetwork_logo' => array(
            'title' => $lang->socialnetwork_settings_logo_tit,
            'description' => $lang->socialnetwork_settings_logo,
            'optionscode' => 'text',
            'value' => 'social/logo.png', // Default
            'disporder' => 6
        ),
        'socialnetwork_defaultavatar' => array(
            'title' => $lang->socialnetwork_settings_defavatar_tit,
            'description' => $lang->socialnetwork_settings_defavatar,
            'optionscode' => 'text',
            'value' => 'social/profil_leer.png', // Default
            'disporder' => 7
        ),
        'socialnetwork_avasize' => array(
            'title' =>  $lang->socialnetwork_settings_avasize_tit,
            'description' => $lang->socialnetwork_settings_avasize,
            'optionscode' => 'text',
            'value' => '150,150', // Default
            'disporder' => 8
        ),
        'socialnetwork_titlesize' => array(
            'title' => 'Titelbildgröße?',
            'description' => $lang->socialnetwork_settings_titlesize,
            'optionscode' => 'text',
            'value' => '600,180', // Default
            'disporder' => 9
        ),
        'socialnetwork_alertpn' => array(
            'title' => $lang->socialnetwork_settings_pn_tit,
            'description' => $lang->socialnetwork_settings_pn,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 10
        ),
        'socialnetwork_alertAlert' => array(
            'title' => $lang->socialnetwork_settings_alert_tit,
            'description' => $lang->socialnetwork_settings_alert,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 11
        ),
        'socialnetwork_uploadImg' => array(
            'title' => $lang->socialnetwork_settings_upload_tit,
            'description' => $lang->socialnetwork_settings_upload,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 12
        ),
        'socialnetwork_uploadImgSize' => array(
            'title' => $lang->socialnetwork_settings_filesize_tit,
            'description' => $lang->socialnetwork_settings_filesize,
            'optionscode' => 'text',
            'value' => '2000000', // Default
            'disporder' => 13
        ),
        'socialnetwork_uploadImgWidth' => array(
            'title' => $lang->socialnetwork_settings_uploadWidth_tit,
            'description' => $lang->socialnetwork_settings_uploadWidth,
            'optionscode' => 'text',
            'value' => '400', // Default
            'disporder' => 14
        ),
        'socialnetwork_uploadImgHeight' => array(
            'title' => $lang->socialnetwork_settings_uploadHeight_tit,
            'description' => $lang->socialnetwork_settings_uploadHeight,
            'optionscode' => 'text',
            'value' => '200', // Default
            'disporder' => 15
        ),
        'socialnetwork_scrolling' => array(
            'title' => $lang->socialnetwork_settings_scrolling_tit,
            'description' => $lang->socialnetwork_scrolling,
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 16
        ),
    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }
    rebuild_settings();

    //add templates and stylesheets
    // Add templategroup
    $templategrouparray = array(
        'prefix' => 'Socialnetwork',
        'title'  => $db->escape_string($lang->socialnetwork_tplgroup),
        'isdefault' => 1
    );
    $db->insert_query("templategroups", $templategrouparray);

    socialnetwork_addtemplates();
    socialnetwork_addstylesheets();
}

function socialnetwork_activate()
{
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    //add variables to member_profile to show link to social network
    find_replace_templatesets("member_profile", "#" . preg_quote('{$userstars}') . "#i", '{$userstars}{$social_link}');

    // add Alerts
    if (function_exists('myalerts_is_activated') && myalerts_is_activated()) {

        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypePost = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertTypePost->setCanBeUserDisabled(true);
        $alertTypePost->setCode("sn_Post");
        $alertTypePost->setEnabled(true);
        $alertTypeManager->add($alertTypePost);

        $alertTypeAnswer = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertTypeAnswer->setCanBeUserDisabled(true);
        $alertTypeAnswer->setCode("sn_Answer");
        $alertTypeAnswer->setEnabled(true);
        $alertTypeManager->add($alertTypeAnswer);

        $alertTypeLike = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertTypeLike->setCanBeUserDisabled(true);
        $alertTypeLike->setCode("sn_Like");
        $alertTypeLike->setEnabled(true);
        $alertTypeManager->add($alertTypeLike);

        $alertTypeFriend = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertTypeFriend->setCanBeUserDisabled(true);
        $alertTypeFriend->setCode("sn_Friend");
        $alertTypeFriend->setEnabled(true);
        $alertTypeManager->add($alertTypeFriend);

        $alertTypeMention = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertTypeMention->setCanBeUserDisabled(true);
        $alertTypeMention->setCode("sn_Mention");
        $alertTypeMention->setEnabled(true);
        $alertTypeManager->add($alertTypeMention);
    }
}

function socialnetwork_deactivate()
{
    //remove template variables, so that it isn't shown anymore
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$social_link}') . "#i", '');
    //remove alerts
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }
        $alertTypeManager->deleteByCode('sn_Post');
        $alertTypeManager->deleteByCode('sn_Answer');
        $alertTypeManager->deleteByCode('sn_Like');
        $alertTypeManager->deleteByCode('sn_Friend');
        $alertTypeManager->deleteByCode('sn_Mention');
    }
}

function socialnetwork_uninstall()
{
    global $db, $cache;
    //remove tables
    if ($db->table_exists("sn_users")) $db->drop_table("sn_users");
    if ($db->table_exists("sn_posts"))  $db->drop_table("sn_posts");
    if ($db->table_exists("sn_answers")) $db->drop_table("sn_answers");
    if ($db->table_exists("sn_friends")) $db->drop_table("sn_friends");
    if ($db->table_exists("sn_likes")) $db->drop_table("sn_likes");
    //remove templates
    $db->delete_query("templates", "title LIKE 'socialnetwork_%'");
    $db->delete_query("templategroups", "prefix = 'Socialnetwork'");

    //remove settings
    $db->delete_query('settings', "name LIKE 'socialnetwork%_'");
    $db->delete_query('settinggroups', "name = 'socialnetwork'");

    //remove stylesheet löschen
    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'socialnetwork.css'");
    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }

    //remove settings
    if ($db->field_exists("socialnetwork_isallowed", "usergroups")) {
        $db->write_query("ALTER TABLE `" . TABLE_PREFIX . "usergroups` DROP `socialnetwork_isallowed`;");
    }
    if ($db->field_exists("socialnetwork_canedit", "usergroups")) {
        $db->write_query("ALTER TABLE `" . TABLE_PREFIX . "usergroups` DROP `socialnetwork_canedit`;");
    }
    if ($db->field_exists("socialnetwork_canmoderate", "usergroups")) {
        $db->write_query("ALTER TABLE `" . TABLE_PREFIX . "usergroups` DROP `socialnetwork_canmoderate`;");
    }
    $cache->update_usergroups();
}

/** 
 * add templates
 * */
function socialnetwork_addtemplates()
{
    global $db, $mybb;
    $template[0] = array(
        "title" => 'socialnetwork_member_main',
        "template" => '<html>
        <head>
        <title>{$lang->socialnetwork_view}</title>
        <link rel="stylesheet" href="social/css/bootstrap.min.css">
        
            
        {$headerinclude}
            <script src="social/js/script.js"></script>
            <script src="social/js/jquery.inview.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <!--  <link rel="stylesheet" href="/resources/demos/style.css">
          <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
          <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->
        </head>
        <body>
        {$header}
        <div class="socialmain">
        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
            <tr>
            <td class="trow1">
                <div class="container">
                <div class="row">
                    <div class="col-12">
                         <div class="sn_titel" style="background:url({$tit_img});height:{$sn_titlesizeheight};"></div>
                        <div class="sn_profil" style="width:{$sn_avasizewidth};height:{$sn_avasizeheight};">{$profil_img}</div>
                        <div class="sn_username"><h1>{$sn_nickname}</h1></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
        <div class="sn_links">
                        <div class="sn_logo"><img src="social/logo_150px.png" alt="social logo"/></div>
                          {$socialnetwork_member_infobit}
                        {$socialnetwork_member_friendsAddDelete}
                        </div>
                        {$socialnetwork_member_friends}
                    </div>
                    <div class="col-8" class="posts">
                    <div class="sn_rechts">
                      <fieldset>
                         <legend>Beitrag erstellen</legend>
                        <!--action=profile&uid=7&area=socialnetwork-->
                         <form method="post" enctype="multipart/form-data" name="picform" id="picform" method="post"> <!--ohne action?-->
                        <!--<form name="uploadformular" enctype="multipart/form-data" action="dateiupload.php" method="post">-->	 
                         <input type="date" value="2017-08-01" name="datum" /> <input type="time" name="sn_uhrzeit" value="12:00" /><br />
                         <textarea id="sn_post" name="sn_post" rows="4" cols="50"></textarea><br />
        <div id="suggest" style="display:none;"></div><br>
                        <input type="file" name="uploadImg" size="60" maxlength="255"><br />
                        <input class="sn_send" type="submit" name="sendPost" value="senden">
                         </form>
                        </fieldset>
                        </div>
                        {$socialnetwork_member_postbit}
                        <div  id="posts" style="width:100%">
                        </div>
                        <input type="hidden" id="pageno" value="1">
                        <input type="hidden" id="activepage" value="{$mybb->input[\\\'uid\\\']}">
                        {$infinitescrolling}
        
                    </div>
                    
        
                  </div>
                </div>				
        </td>
        </tr>
        </table>
            </div>
                {$footer}
            </body>
        </html>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[1] = array(
        "title" => 'socialnetwork_member_postbit',
        "template" => '<div class="sn_rechts">
        <fieldset>
            <div class="container snpost">
            <div class="row snpost">
                <div class="col-1">
                    <a id="{$sn_postid}"></a>
                    <img class="sn_postProfilbild" src="{$sn_postimg}" alt="" />
                </div>
                <div class="col-11">
                <span class="sn_postName">{$sn_postname}</span>
                <span class="sn_postDate">{$sn_date}</span>
                    <span class="sn_edit">{$sn_post_ed_del}</span>
                    <div class="sn_socialPost" id="p{$sn_postid}">{$sn_showPost}</div>
                    {$socialnetwork_member_postimg}
                <div class="sn_likes">
                Gefällt {$cnt_likes_post} Mal <a href="member.php?action=profile&uid={$activepage}&area=socialnetwork&like={$likevar}&postid={$sn_postid}&ansid=0">{$sn_like}</a>
    
                </div>
                    {$socialnetwork_member_answerbit}
                    <div class="sn_answer_form">
                        <form action="" method="post">
                        <input type="hidden"  value="{$sn_postid}" name="postid" />
                        <img class="sn_answerFormProfilbild" src="{$sn_ansFormImg}" alt="" />
                        <input type="date" value="2017-08-01" name="sn_ansDatum" /> <input type="time" name="sn_ansUhrzeit" value="12:00" /><br />
                        <textarea id="sn_answer" name="sn_answer" rows="1" cols="60"></textarea><br />
                        <input class="sn_send" type="submit" name="sendAnswer" value="senden">
                        </form>
                    </div>
                </div>
                </div>
            </div>
        </fieldset>
    </div>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[2] = array(
        "title" => 'socialnetwork_member_postimg',
        "template" => '<div class="sn_img">
        <a href="#popinfo{$postImg[\\\'sn_imgId\\\']}"><img src="social/userimages/{$postImg[\\\'sn_filename\\\']}" style="max-width:98%; max-height:300px;" /></a>
        </div>
        <div id="popinfo{$postImg[\\\'sn_imgId\\\']}" class="infopop">
          <div class="pop"><img src="social/userimages/{$postImg[\\\'sn_filename\\\']}" style="max-width:100%; max-height:100%;" /></div><a href="#closepop" class="closepop"></a>
        </div>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[2] = array(
        "title" => 'socialnetwork_member_postedit',
        "template" => '<input type="button" class="editDelete" name="editpost" onclick="change({$sn_postid},\\\'{$sn_date_date}\\\',\\\'{$sn_date_time}\\\')" value="[e]"/>
        <a href="member.php?action=profile&uid={$activepage}&area=socialnetwork&postdelete={$sn_postid}" class="editDelete" >[d]</a>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[3] = array(
        "title" => 'socialnetwork_ucp_main',
        "template" => '<html>
        <head>
        <title>{$lang->socialnetwork_usercp}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <table width="100%" border="0" align="center">
        <tr>
        {$usercpnav}
        <td valign="top">
        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
        <tr>
            <td class="thead" colspan="2"><strong>Verwaltung - Soziales Netzwerk</strong></td>
        </tr>
        <tr>
        <td class="trow2">
            <div class="ucp_social">
			<form method="post" action="usercp.php">
            <fieldset>
            <legend>Benachrichtigungseinstellungen</legend>
            <input type="checkbox" name="alertPost" {$sn_postcheck}> bei neuem Post oder Antwort.<br>
            <input type="checkbox" name="alertLike" {$sn_likecheck}> wenn jemanden ein Post oder eine Antwort von dir gefällt.<br>
            <input type="checkbox" name="alertFriend" {$sn_friendcheck}> wenn jeman dein Freund sein will.<br>
            <input type="checkbox" name="alertMention" {$sn_mentioncheck}> wenn dich jemand erwähnt.</br>
            </fieldset>
        
            <fieldset>
                <legend>Charakterinformationen</legend>
                <label>Nickname:</label> <input type="text" name="nickname" value="{$nickname}"/><br />
				<label>Avatar:<div class="ucp_smallinfo">Avatargröße: {$sizes[0]}x{$sizes[1]}px</div></label> <input type="text" name="profilbild" value="{$profilbild}"/><br />
                <label>Titelbild:<div class="ucp_smallinfo">Titelbildgröße: {$sizes[2]}x{$sizes[3]}px</div></label> <input type="text" name="titelbild" value="{$titelbild}"/><br />
                
            </fieldset>
            
            <fieldset>
                <legend>Weitere Felder:</legend>
                {$socialnetwork_ucp_ownFieldsBit}
            </fieldset>
            
            <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
            <input type="hidden" name="action" value="editsn_do" />
            <input type="submit" value="Speichern" name="Speichern" class="button" />
			</form>
            </div>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        {$footer}
        </body>
        </html>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[4] = array(
        "title" => 'socialnetwork_ucp_nav',
        "template" => '<tr><td class="trow1 smalltext"><a href="usercp.php?action=socialnetwork">Soziales Netzwerk</a></td></tr>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[5] = array(
        "title" => 'socialnetwork_newsfeed_all',
        "template" => 'NEWSFEED FROM ALL CHARAS ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[6] = array(
        "title" => 'socialnetwork_newsfeed_friends',
        "template" => 'NEWSFEED FROM FRIENDS ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[7] = array(
        "title" => 'socialnetwork_ucp_ownFieldsBit',
        "template" => '<label>{$sn_fieldtitle}:</label> <input type="text" name="{$sn_fieldtitle}" value="{$get_input}"/><br />',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[8] = array(
        "title" => 'socialnetwork_member_infobit',
        "template" => '<sn_tit>{$own_title}:</sn_tit> {$own_value} <br/>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[9] = array(
        "title" => 'socialnetwork_member_postbit',
        "template" => '<div class="sn_rechts">
        <fieldset>
            <img class="sn_postProfilbild" src="{$sn_postimg}" alt="" />
            <span class="sn_postName">{$sn_postname}</span>
            <span class="sn_postDate">{$sn_date}</span>
            <span class="sn_edit">{$edit} {$delete}</span>
            <div class="sn_socialPost">{$sn_showPost}</div>
            {$socialnetwork_member_answerbit}
            <hr>
            <div class="sn_answer_form">
                <form action="" method="post">
                <input type="hidden"  value="{$sn_postid}" name="postid" />
                <img class="sn_answerFormProfilbild" src="{$sn_ansFormImg}" alt="" />
                <input type="date" value="2017-08-01" name="sn_ansDatum" /> <input type="time" name="sn_ansUhrzeit" value="12:00" /><br />
                <textarea id="sn_answer" name="sn_answer" rows="1" cols="60"></textarea><br />
                <input class="sn_send" type="submit" name="sendAnswer" value="senden">
                </form>
            </div>
        </fieldset>
    </div>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[10] = array(
        "title" => 'socialnetwork_member_friendsbitToAccept',
        "template" => '<div class="sn_friend">
        <img src="{$friendava}" width="35px"/>  {$friendname} 
         <a href="member.php?action=profile&uid={$activepage}&area=socialnetwork&friend=accept&friendid={$friend}"><span class="fas fa-user-check" aria-label="accept"></span></a>
     <a href="member.php?action=profile&uid={$activepage}&area=socialnetwork&friend=deny&friendid={$friend}"><span class="fas fa-user-times" aria-label="deny"></span></a></div>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[11] = array(
        "title" => 'socialnetwork_member_friendsbitAsked',
        "template" => '<div class="sn_friend">
        <img src="{$friendava}" width="35px"/>  {$friendname} 
    </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[12] = array(
        "title" => 'socialnetwork_member_friendsbit',
        "template" => '<div class="sn_friend"><img src="{$friendava}" width="35px"/> {$friendname} {$frienddelete}</div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[13] = array(
        "title" => 'socialnetwork_member_friends',
        "template" => '<div class="sn_links"><h1 class="friends">Friends</h1>
        {$socialnetwork_member_friendsbit}
        {$friendsToAcceptTitle}
        {$socialnetwork_member_friendsbitToAccept}
        {$socialnetwork_member_friendsbitAsked}
        </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[14] = array(
        "title" => 'socialnetwork_member_answeredit',
        "template" => '<input type="button" class="editDelete" name="editans" onclick="changeAns({$ansid},\\\'{$ansdate}\\\',\\\'{$anstime}\\\')" value="[e]"/>
        <input type="button" name="delans" class="editDelete" value="[d]"/>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[15] = array(
        "title" => 'socialnetwork_member_answerbit',
        "template" => '<div class="sn_answer">
        <a id="ans_{$ansid}"></a>
            <input type="hidden" id="ans_{$ansid}" value="{$ansid}" name="ansid" />
            <img class="sn_ansProfilbild" src="{$sn_anspostimg}" alt="" />
            <span class="sn_ansName">{$sn_ansname}</span>
            <span class="sn_ansDate">{$sn_ansdate}</span>
            <span class="sn_edit">{$sn_ans_ed_del}</span>
            <div class="sn_socialAnswer" id="a{$ansid}">{$sn_showAnswer}</div>
    </div>
                <div class="sn_likes">
                Gefällt {$cnt_likes_ans} Mal <a href="member.php?action=profile&uid={$activepage}&area=socialnetwork&like={$likevar_ans}&postid=0&ansid={$ansid}">{$sn_like_ans}</a>
                </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );



    foreach ($template as $row) {
        $db->insert_query("templates", $row);
    }
}
/**
 * add stylesheet
 */
function socialnetwork_addstylesheets()
{
    global $db;
    $css = array(
        'name' => 'socialnetwork.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" =>    '.socialmain {
            text-align:center;
        }
        
        .sn_username {
            padding-left: 10px;
        }
        
        legend{
        width: auto;
        }
        
        .sn_titel{
            width: 100%;
            border: 0px #b1b1b1 solid;
            background-repeat: no-repeat !important;
            background-position: center 0px !important;
        }
        
        .sn_profil{
            background-color: #b1b1b1;
            padding: 5px;
            margin-left: 70px;
            margin-top: -100px;
            margin-right: 10px;
            float: left;
        }
        
        .sn_links{
            background-color: #b1b1b1;
            margin: 10px;
            padding:10px;
            height: min-content;
        }
        
        .sn_logo{
            margin:auto;
            text-align:center;
        }
        
        .sn_rechts{
            background-color: #b1b1b1;
            margin: 10px;
            padding: 10px
        }
                
        .ucp_social legend{
            font-weight: bold;
        }
        
        sn_tit {
        font-weight: bold;
        
        }
        
        .ucp_social label{
            display: block;
            width: 120px;
            float: left; 
            clear: left;
        }
                
        .ucp_social input{
            margin: 5px;
        }
            
        .ucp_smallinfo {
            font-size: 0.7em;
        }',
        'cachefile' => $db->escape_string(str_replace('/', '', 'socialnetwork.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }
}

/**
 * Gruppenberechtigungen
 */
$plugins->add_hook("admin_formcontainer_end", "socialnetwork_editgroup");
function socialnetwork_editgroup()
{
    global $run_module, $form_container, $lang, $form, $mybb, $user;

    $lang->load("socialnetwork");

    if ($run_module == 'user' && !empty($form_container->_title) && !empty($lang->users_permissions) && $form_container->_title == $lang->users_permissions) {
        $socialnetwork_options = array();
        $socialnetwork_options[] = $form->generate_check_box('socialnetwork_isallowed', 1, $lang->socialnetwork_perm_base, array('checked' => $mybb->input['socialnetwork_isallowed']));
        $socialnetwork_options[] = $form->generate_check_box('socialnetwork_canedit', 1, $lang->socialnetwork_perm_edit, array('checked' => $mybb->input['socialnetwork_canedit']));
        $socialnetwork_options[] = $form->generate_check_box('socialnetwork_canmoderate', 1, $lang->socialnetwork_perm_mod, array('checked' => $mybb->input['socialnetwork_canmoderate']));

        $form_container->output_row($lang->socialnetwork_perm, '', '<div class="group_settings_bit">' . implode('</div><div class="group_settings_bit">', $socialnetwork_options) . '</div>');
    }
}

/*
*	Gruppenberechtigungen Speichern
*/
$plugins->add_hook("admin_user_groups_edit_commit", "socialnetwork_editgroupdo");
function socialnetwork_editgroupdo()
{
    global $updated_group, $mybb;

    $updated_group['socialnetwork_isallowed'] = intval($mybb->input['socialnetwork_isallowed']);
    $updated_group['socialnetwork_canedit'] = intval($mybb->input['socialnetwork_canedit']);
    $updated_group['socialnetwork_canmoderate'] = intval($mybb->input['socialnetwork_canmoderate']);
}

/*
 * Fügt die Verwaltung des Social Networks ins UCP Menü ein 
 */
$plugins->add_hook("usercp_menu", "socialnetwork_usercp_menu");
function socialnetwork_usercp_menu()
{
    global $templates, $mybb, $cache, $socialnetwork_ucp_nav;
    $usergroups_cache = $cache->read("usergroups");

    if ($usergroups_cache[$mybb->user['usergroup']]['socialnetwork_isallowed'] && $usergroups_cache[$mybb->user['usergroup']]['socialnetwork_canedit']) {
        eval("\$socialnetwork_ucp_nav .= \"" . $templates->get("socialnetwork_ucp_nav") . "\";");
        $templates->cache["usercp_nav_misc"] = str_replace(
            "<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">",
            "<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">{$socialnetwork_ucp_nav}",
            $templates->cache["usercp_nav_misc"]
        );
    }
}
/*
*	UserCP
*	This function handles everything related to the user Cp
*/
$plugins->add_hook("usercp_start", "socialnetwork_usercp");
function socialnetwork_usercp()
{
    global $db, $mybb, $lang, $cache, $templates, $page, $theme, $headerinclude, $header, $footer, $usercpnav;
    $lang->load('socialnetwork');
    $usergroups_cache = $cache->read("usergroups");
    $thisUser = intval($mybb->user['uid']);

    if ($mybb->input['action'] == "socialnetwork") {
        add_breadcrumb($lang->nav_usercp, "usercp.php");
        add_breadcrumb($lang->changeuserpage, "usercp.php?action=socialnetwork");
        $linktosocial = '<span class="smalltext"><a href="member.php?action=profile&uid=' . $thisUser . '&area=socialnetwork">' . $lang->socialnetwork_ucp_link . '</a></span>';

        $sizes = get_avatit_size();
        $sn_avasizewidth = $sizes[0] . "px";
        $sn_avasizeheight = $sizes[1] . "px";
        $sn_titlesizewidth = $sizes[2] . "px";
        $sn_titlesizeheight = $sizes[3] . "px";

        //user is not allowed to use social network
        if (!$usergroups_cache[$mybb->user['usergroup']]['socialnetwork_isallowed'] || !$usergroups_cache[$mybb->user['usergroup']]['socialnetwork_canedit']) {
            error_no_permission();
        }
        //get the inputs and settings of user
        $get_input = $db->query("SELECT * FROM " . TABLE_PREFIX . "sn_users WHERE uid = " . $thisUser . "");
        while ($input = $db->fetch_array($get_input)) {
            $nickname = $input['sn_nickname'];
            $profilbild = $input['sn_avatar'];
            $titelbild = $input['sn_userheader'];
            $sn_alertPost = $input['sn_alertPost'];
            $sn_alertFriend = $input['sn_alertFriend'];
            $sn_alertLike = $input['sn_alertLike'];
            $sn_alertMention = $input['sn_alertMention'];
        }

        if ($sn_alertPost == 1) $sn_postcheck = "checked";
        else $sn_postcheck = "";
        if ($sn_alertFriend == 1) $sn_likecheck = "checked";
        else $sn_likecheck = "";
        if ($sn_alertLike == 1) $sn_friendcheck = "checked";
        else $sn_friendcheck = "";
        if ($sn_alertMention == 1) $sn_mentioncheck = "checked";
        else $sn_sn_mentioncheck = "";

        $fields = getOwnFields();
        if (empty($fields)) $socialnetwork_ucp_ownFieldsBit = "Keine weiteren Felder.";

        foreach ($fields as $field) {
            $sn_fieldtitle = $field;
            $get_input  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $mybb->user['uid']), "own_" . $field);
            eval("\$socialnetwork_ucp_ownFieldsBit .= \"" . $templates->get('socialnetwork_ucp_ownFieldsBit') . "\";");
        }
        eval("\$page = \"" . $templates->get('socialnetwork_ucp_main') . "\";");
        output_page($page);
    }

    if ($mybb->input['action'] == "editsn_do" && $mybb->request_method == "post") {
        verify_post_check($mybb->input['my_post_key']);
        //preparing for insert in table
        //handle of checkboxes
        if (isset($mybb->input['alertPost'])) $alertPost = "1";
        else $alertPost = "0";
        if (isset($mybb->input['alertLike'])) $alertLike = "1";
        else $alertLike = "0";
        if (isset($mybb->input['alertFriend'])) $alertFriend = "1";
        else $alertFriend = "0";
        if (isset($mybb->input['alertMention'])) $alertMention = "1";
        else $alertMention = "0";

        //handle of the default values
        $nickname = $db->escape_string($mybb->input['nickname']);
        $avatar = $db->escape_string($mybb->input['profilbild']);
        $titelbild = $db->escape_string($mybb->input['titelbild']);
        //the funny part, handle of the dynamic fields
        //get them
        $ownfields = getOwnFields();
        //some intial stuff
        $strOwnFields = "";
        $strownIns = "";
        $strUpdate = "";
        //are there own fields? 
        if (!empty($ownfields)) {
            //we need some strings to make our query work
            $strownIns = ",";
            $strOwnFields = ",";
            $strUpdate = ",";
            //and now we have to puzzle
            foreach ($ownfields as $ownfield) {
                $strOwnFields .= "own_" . $ownfield . ",";
                $strownIns .= "'" . $db->escape_string($mybb->input[$ownfield]) . "',";
                $inputvalue = $db->escape_string($mybb->input[$ownfield]);
                $strUpdate .= "own_" . $ownfield . "='" . $inputvalue . "',";
            }
            //we don't want the last , so cut it off
            $strOwnFields = substr($strOwnFields, 0, -1);
            $strownIns = substr($strownIns, 0, -1);
            $strUpdate = substr($strUpdate, 0, -1);
        }

        $db->write_query("INSERT INTO " . TABLE_PREFIX . "sn_users(uid, sn_nickname, sn_avatar, sn_userheader, sn_alertPost, sn_alertFriend,sn_alertLike,sn_alertMention" . $strOwnFields . ") 
        VALUES 
        ('$thisUser', '$nickname','$avatar','$titelbild','$alertPost','$alertFriend','$alertLike','$alertMention'" . $strownIns . ") 
        ON DUPLICATE KEY UPDATE 
        sn_nickname='$nickname', sn_avatar='$avatar', sn_userheader='$titelbild', 
        sn_alertPost = '$alertPost', sn_alertLike='$alertLike', sn_alertFriend='$alertFriend', sn_alertMention ='$alertMention'
        " . $strUpdate . "");

        redirect('usercp.php?action=socialnetwork');
    }
}

//WIP Anzeige Profil
/***
 * The Mainpage of Network, bundle all the work
 */
$plugins->add_hook("member_profile_start", "socialnetwork_mainpage");
function socialnetwork_mainpage()
{
    global $db, $mybb, $lang, $templates, $infinitescrolling, $cache, $page, $headerinclude, $header, $footer, $usercpnav, $theme, $socialnetwork_member_postbit, $socialnetwork_member_friendsbit, $socialnetwork_member_postimg, $socialnetwork_member_friends, $socialnetwork_member_friendsAddDelete;
    $lang->load('socialnetwork');
    $usergroups_cache = $cache->read("usergroups");
    $thisuser = intval($mybb->user['uid']);

    if ($mybb->input['area'] == "socialnetwork") {
        //not allowed to use social network
        if (!$usergroups_cache[$mybb->user['usergroup']]['socialnetwork_isallowed']) {
            error_no_permission();
        }
        //getsettings
        $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
        $socialnetwork_scrolling = $mybb->settings['socialnetwork_scrolling'];
        //get sizes
        $sizes = get_avatit_size();
        $sn_avasizewidth = $sizes[0] + 10 . "px"; // +10 for nice view with padding
        $sn_avasizeheight = $sizes[1] + 10 . "px"; // +10 for nice view with padding
        $sn_titlesizewidth = $sizes[2] . "px";
        $sn_titlesizeheight = $sizes[3] . "px";

        //Get the data of the page, we are looking at
        $pagedata = $db->simple_select("sn_users", "*", "uid = " . intval($mybb->input['uid']));
        //user habe no page
        if ($db->num_rows($pagedata) == 0) error_no_permission();
        $sn_activepage = $db->fetch_array($pagedata);

        //catch data
        if ($sn_activepage['sn_userheader'] == "") $tit_img = "";
        else $tit_img = $sn_activepage['sn_userheader'];
        if ($sn_activepage['sn_avatar'] == "") $profil_img = "<img src=\"" . $defaultava . "\"/>";
        else $profil_img = "<img src=\"" . $sn_activepage['sn_avatar'] . "\"/>";
        if ($sn_activepage['sn_nickname'] == "") $sn_nickname = $db->fetch_field($db->simple_select("users", "username", "uid = " . $sn_activepage['uid'], "limit 1"), "username");
        else $sn_nickname = $sn_activepage['sn_nickname'];

        $socialnetwork_view = $lang->socialnetwork_view;
        $lang->socialnetwork_view = $lang->sprintf($socialnetwork_view, $sn_nickname);

        //Now we want the individual fields
        $fields = getOwnFields();
        // $socialnetwork_ucp_ownFieldsBit
        foreach ($fields as $field) {
            $sn_fieldtitle = $field;
            $get_value  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $sn_activepage['uid']), "own_" . $field);
            $own_title = $field;
            if ($get_value == "") $own_value = $lang->socialnetwork_member_ownNotFilled;
            else $own_value = $get_value;
            eval("\$socialnetwork_member_infobit .= \"" . $templates->get('socialnetwork_member_infobit') . "\";");
        }
        $thispage = intval($sn_activepage['uid']);
        if (isset($mybb->input['sendPost'])) {
            //check if theres an upload

            $postid = $db->fetch_field($db->write_query("Select max(sn_post_id) as max FROM " . TABLE_PREFIX . "sn_posts LIMIT 1"), "max");
            if (isset($_FILES['uploadImg']['name']) && $_FILES['uploadImg']['name'] != '') {
                uploadImg($postid + 1);
            }
            $datetime = $db->escape_string($mybb->input['datum'] . " " . $mybb->input['sn_uhrzeit']);
            $post = $db->escape_string($mybb->input['sn_post']);

            checkMentions("post", $thispage, $thisuser, $postid + 1, 0);
            if ($post != '') {
                mentionUser($post, $thispage, $postid + 1, 0);
                savingPostOrAnswer($thispage, $thisuser, $datetime, $post, "sn_posts");
            } else {
                echo "<script>alert('" . $lang->socialnetwork_member_errorMessageEmpty . ".');</script>";
            }
            // redirect('member.php?action=profile&uid='.$sn_activepage['uid'].'&area=socialnetwork');
        }

        if (isset($mybb->input['sendAnswer'])) {
            $toPostId = intval($mybb->input['postid']);
            $answerid = intval($db->fetch_field($db->write_query("Select max(sn_aid) as max FROM " . TABLE_PREFIX . "sn_answers LIMIT 1"), "max"));
            $datetime = $db->escape_string($mybb->input['sn_ansDatum'] . " " . $mybb->input['sn_ansUhrzeit']);
            $answer = $db->escape_string($mybb->input['sn_answer']);

            checkMentions("answer", $thispage, $thisuser, $toPostId, $answerid + 1);
            if ($answer != '') {
                mentionUser($answer, $thispage, $toPostId, $answerid + 1);
                savingPostOrAnswer($toPostId, $thisuser, $datetime, $answer, "sn_answers");
            } else {
                echo "<script>alert('" . $lang->socialnetwork_member_errorMessageEmpty . ".');</script>";
            }
        }

        if (isset($mybb->input['saveEditPost'])) {
            $message = $db->escape_string($mybb->input['editPost']);
            $id = intval($mybb->input['sn_postEditId']);
            $datetime = $db->escape_string($mybb->input['sn_postDatumEdit'] . " " . $mybb->input['sn_postUhrzeitEdit']);
            if ($message != '') {
                updatePostOrAnswer($id, $datetime, $message, "sn_posts");
            }
        }
        if ((isset($mybb->input['saveEditAns']))) {
            $messageAns = $db->escape_string($mybb->input['editAnswer']);
            $idAns = intval($mybb->input['sn_ansEditId']);
            $datetimeAns = $db->escape_string($mybb->input['sn_ansDatumEdit'] . " " . $mybb->input['sn_ansUhrzeitEdit']);
            if ($messageAns != '') {
                updatePostOrAnswer($idAns, $datetimeAns, $messageAns, "sn_answers");
            }
        }

        $sn_postid = intval($mybb->input['postid']);
        $sn_ansid = intval($mybb->input['ansid']);
        $sn_uid = intval($mybb->user['uid']);

        if ($mybb->input['like'] == 'like') {
            checkMentions("like", $thispage, $thisuser, $sn_postid, $sn_ansid);
            like($thispage, $sn_postid, $sn_ansid, $sn_uid);
        }
        if ($mybb->input['like'] == 'dislike') {
            dislike($thispage, $sn_postid, $sn_ansid, $sn_uid);
        }

        if ($mybb->input['postdelete'] != "" && is_numeric($mybb->input['postdelete'])) {
            $toDelete = intval($mybb->input['postdelete']);
            deletePost($toDelete);
        }
        if ($mybb->input['ansdelete'] != "" && is_numeric($mybb->input['ansdelete'])) {
            $toDelete = intval($mybb->input['ansdelete']);
            deleteAnswer($toDelete);
        }
        showFriends();
        //infinite scrolling or without?  
        if ($socialnetwork_scrolling == 1) {
            showPostAjax();
        } else {
            showPosts();
        }
        eval("\$page = \"" . $templates->get('socialnetwork_member_main') . "\";");
        output_page($page);
        die();
    }
}

function deletePost($toDelete)
{
    global $db, $mybb, $lang;
    $thisuser = intval($mybb->user['uid']);
    $postuid = $db->fetch_field($db->simple_select("sn_posts", "sn_uid", "sn_post_id = $toDelete"), "sn_uid");
    if (($thisuser == $postuid) || ($mybb->usergroup['canmodcp'] == 1)) {
        $db->delete_query("sn_posts", "sn_post_id = $toDelete");
        $db->delete_query("sn_answers", "sn_post_id = $toDelete");
    } else {
        echo "<script>alert('" . $lang->socialnetwork_member_errorMessageDelete . "')</script>";
    }
}

function deleteAnswer($toDelete)
{
    global $db, $mybb, $lang;
    $thisuser = intval($mybb->user['uid']);
    $postuid = $db->fetch_field($db->simple_select("sn_answers", "sn_uid", "sn_aid = $toDelete"), "sn_uid");
    if (($thisuser == $postuid) || ($mybb->usergroup['canmodcp'] == 1)) {
        $db->delete_query("sn_answers", "sn_aid = $toDelete");
    } else {
        echo "<script>alert('" . $lang->socialnetwork_member_errorMessageDelete . "')</script>";
    }
}

//WIP MYAlert Integration

//TODO Verwaltung MOD CP
//

/**
 * //TODO handle when user is deleted -> nickname empty? save username, else keep nickname
 * Was passiert wenn ein User gelöscht wird?
 * Was passiert mit den geposteten Beiträgen?
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "socialnetwork_userdelete");
function socialnetwork_userdelete()
{
    //posts umändern -> del_nickname etc
    //aus friends löschen
}

/*
 *  Verwaltung der Defaults im Tool Menü des ACP hinzufügen
 *  freien index finden
 */
$plugins->add_hook("admin_tools_menu", "socialnetwork_menu");
function socialnetwork_menu($sub_menu)
{
    $key = count($sub_menu) * 10 + 10; /* We need a unique key here so this works well. */
    $sub_menu[$key] = array(
        'id'    => 'SozialesNetzwerk',
        'title'    => 'Soziales Netzwerk Verwaltung',
        'link'    => 'index.php?module=tools-socialnetwork'
    );
    return $sub_menu;
}

$plugins->add_hook("admin_tools_action_handler", "socialnetwork_action_handler");
function socialnetwork_action_handler($actions)
{
    $actions['socialnetwork'] = array('active' => 'socialnetwork', 'file' => 'socialnetwork.php');
    return $actions;
}


/*** Other functions 
 * put it in extra functions for better handling
 * ***/

/****
 * Upload of images
 *****/
function uploadImg($post)
{
    global $db, $mybb, $lang;

    $uploadImgWidth = $mybb->settings['socialnetwork_uploadImgWidth'];
    $uploadImgHeight = $mybb->settings['socialnetwork_uploadImgHeight'];
    $maxfilesize = $mybb->settings['socialnetwork_uploadImgSize'];

    //echo($post);
    if ($post == "") $post = 1;
    $imgpath = "social/userimages/";
    // Check if gallery path is writable
    if (!is_writable('social/userimages/')) echo ($lang->socialnetwork_upload_errorPath);

    $sizes = getimagesize($_FILES['uploadImg']['tmp_name']);
    $failed = false;
    if ($sizes === false) {
        @unlink($imgpath);
        move_uploaded_file($_FILES['uploadImg']['tmp_name'], 'upload/' . $_FILES['uploadImg']['name']);
        $_FILES['uploadImg']['tmp_name'] = $imgpath;
        $sizes = getimagesize($_FILES['uploadImg']['tmp_name']);
        $failed = true;
    }
    // No size, then it's probably not a valid pic.
    if ($sizes === false) echo ($lang->socialnetwork_upload_errorSizes);
    elseif ((!empty($uploadImgWidth) && $sizes[0] >  $uploadImgWidth) || (!empty($uploadImgHeight) && $sizes[1] > $uploadImgHeight)) {
        //Delete the temp file
        @unlink($_FILES['uploadImg']['tmp_name']);
        echo ("Fehler Größe");
    } else {
        $filesize = $_FILES['uploadImg']['size'];

        if (!empty($maxfilesize) && $filesize > $maxfilesize) {
            //Delete the temp file
            @unlink($_FILES['uploadImg']['tmp_name']);
            echo ($lang->socialnetwork_upload_errorFileSize);
        }
        $extensions = array(
            1 => 'gif',
            2 => 'jpeg',
            3 => 'png',
            5 => 'psd',
            6 => 'bmp',
            7 => 'tiff',
            8 => 'tiff',
            9 => 'jpeg',
            14 => 'iff',
        );
        $extension = isset($extensions[$sizes[2]]) ? $extensions[$sizes[2]] : '.bmp';
        $filename = $mybb->user['uid'] . '-' . date('d_m_y_g_i_s') . '.' . $extension;

        if ($failed == false) move_uploaded_file($_FILES['uploadImg']['tmp_name'], $imgpath . $filename);
        else
            rename($_FILES['uploadImg']['tmp_name'], $imgpath . $filename);

        @chmod($imgpath . $filename, 0644);

        $db->write_query("INSERT INTO " . TABLE_PREFIX . "sn_imgs
						(sn_filesize, sn_filename, sn_width, sn_height, sn_uid, sn_postId)
						VALUES ( $filesize,'$filename', $sizes[0], $sizes[1], " . $mybb->user['uid'] . ", $post)");
    }
}

function showPosts()
{
    global  $activepage, $db, $lang, $mybb, $templates, $parser, $socialnetwork_member_postbit, $socialnetwork_member_answerbit, $socialnetwork_member_postimg;
    //Parser options
    $options = array(
        "allow_html" => $mybb->settings['socialnetwork_html'],
        "allow_mycode" => $mybb->settings['socialnetwork_mybbcode'],
        "allow_imgcode" => $mybb->settings['socialnetwork_img'],
        "filter_badwords" => $mybb->settings['socialnetwork_badwords'],
        "nl2br" => 1,
        "allow_videocode" => $mybb->settings['socialnetwork_videos'],
    );

    $activepage = intval($mybb->input['uid']);
    $thisUser = intval($mybb->user['uid']);
    $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
    $queryPosts = $db->simple_select("sn_posts", "*", "sn_pageid = $activepage", array(
        "order_by" => 'sn_date',
        "order_dir" => 'DESC',
    ));
    $cnt_likes_post = "";

    while ($get_post = $db->fetch_array($queryPosts)) {
        $likevar = "like";
        $sn_like = $lang->socialnetwork_member_like;
        //show the image beside the anwser form
        $sn_ansFormImg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$thisUser'"), "sn_avatar");
        if ($sn_ansFormImg == "") $sn_ansFormImg = $defaultava;
        //poster uid
        $postuser = intval($get_post['sn_uid']);

        //did the poster have a nickname if not take the username
        $name =  htmlspecialchars_uni($db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = '$postuser'"), "sn_nickname"));
        if ($name == "") {
            $name =  htmlspecialchars_uni($db->fetch_field($db->simple_select("users", "username", "uid = '$postuser'"), "username"));
        }
        //we want to link to the social page of the poster
        $sn_postname = '<a href="member.php?action=profile&uid=' . $postuser . '&area=socialnetwork">' . $name . '</a>';
        //the avatar
        $sn_postimg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$postuser'"), "sn_avatar");
        if ($sn_postimg == "") $sn_postimg = $defaultava;
        //handle of deleted users
        if ($get_post['sn_del_username'] != "") {
            $sn_postname =  htmlspecialchars_uni($get_post['sn_del_username']);
            if ($get_post['sn_del_nickname'] != "") {
                $sn_postname =  htmlspecialchars_uni($get_post['sn_del_nickname']);
            }
            $sn_postimg = $defaultava;
        }

        //the other informations of post
        $sn_date = date('d.m.y - H:i', strtotime($get_post['sn_date']));

        $sn_showPost = $parser->parse_message($get_post['sn_social_post'], $options);
        $sn_postid = intval($get_post['sn_post_id']);

        $sn_post_ed_del = "";
        //edit and delete
        if (($thisUser == $postuser) || ($mybb->usergroup['canmodcp'] == 1)) {
            $sn_date_date = date('Y-m-d', strtotime($get_post['sn_date']));
            $sn_date_time = date('H:i', strtotime($get_post['sn_date']));
            eval("\$sn_post_ed_del = \"" . $templates->get("socialnetwork_member_postedit") . "\";");
        }

        //we have to clear the variables first
        $socialnetwork_member_answerbit = "";
        $socialnetwork_member_postimg = "";
        //Get all likes
        $likeQuery = $db->simple_select("sn_likes", "*", "sn_postid = $sn_postid");
        while ($likesarray = $db->fetch_array($likeQuery)) {
            //do the user already like the post? -> then we want to show the dislike stuff
            if ($likesarray['sn_uid'] == $thisUser) {
                $likevar = "dislike";
                $sn_like = $lang->socialnetwork_member_dislike;
            }
        }
        //count likes
        $cnt_likes_post = $db->fetch_field($db->simple_select("sn_likes", "count(*) as cnt", "sn_postid = $sn_postid"), "cnt");

        //Do the user upload an image to the post?
        $postImg = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = $sn_postid"));
        if (!empty($postImg)) {
            eval("\$socialnetwork_member_postimg = \"" . $templates->get('socialnetwork_member_postimg') . "\";");
        }
        //variale to count the likes of an answer
        $cnt_likes_ans = "";
        //and here we get the answers for the actual post
        $queryAnswer = $db->simple_select("sn_answers", "*", "sn_post_id = $sn_postid", array(
            "order_by" => 'sn_date',
            "order_dir" => 'DESC'
        ));
        $sn_ans_ed_del = "";
        while ($get_answer = $db->fetch_array($queryAnswer)) {
            //Initial like stuff for answers
            $likevar_ans = "like";
            $sn_like_ans = $lang->socialnetwork_member_like;

            $ansid = intval($get_answer['sn_aid']);
            //count like of answers
            $cnt_likes_ans = $db->fetch_field($db->simple_select("sn_likes", "count(sn_postid) as cnt", "sn_answerid = $ansid"), "cnt");
            //all likes
            $likeQuery = $db->simple_select("sn_likes", "*", "sn_answerid = $ansid");
            while ($likesarray = $db->fetch_array($likeQuery)) {
                if ($likesarray['sn_uid'] == $thisUser) {
                    $likevar_ans = "dislike";
                    $sn_like_ans = $lang->socialnetwork_member_dislike;
                }
            }
            //uid of answer
            $sn_ansUser = intval($get_answer['sn_uid']);
            //avatar 
            $sn_anspostimg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$sn_ansUser'"), "sn_avatar");
            if ($sn_anspostimg == "") $sn_anspostimg = $defaultava;
            //name (nickname or username?)
            $ansname =  htmlspecialchars_uni($db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = '$sn_ansUser'"), "sn_nickname"));
            if ($ansname == "") $ansname =  htmlspecialchars_uni($db->fetch_field($db->simple_select("users", "username", "uid = '$sn_ansUser'"), "username"));
            $sn_ansname = '<a href="member.php?action=profile&uid=' . $sn_ansUser . '&area=socialnetwork">' . $ansname . '</a>';

            //handle of deleted user
            if ($get_answer['sn_del_username'] != "") {
                $sn_ansname =  htmlspecialchars_uni($get_answer['sn_del_username']);
                if ($get_answer['sn_del_nickname'] != "") {
                    $sn_ansname =  htmlspecialchars_uni($get_answer['sn_del_nickname']);
                }
                $sn_anspostimg = $defaultava;
            }
            $sn_ansdate = date('d.m.y - H:i', strtotime($get_answer['sn_date']));
            $sn_ans_ed_del = "";
            //edit and delete
            if (($thisUser == $sn_ansUser) || ($mybb->usergroup['canmodcp'] == 1)) {
                // eval("\$sn_post_ed_del = \"".$templates->get("socialnetwork_member_postedit")."\";");
                $ansdate = date('Y-m-d', strtotime($get_answer['sn_date']));
                $anstime = date('H:i', strtotime($get_answer['sn_date']));
                eval("\$sn_ans_ed_del = \"" . $templates->get("socialnetwork_member_answeredit") . "\";");
            }
            $sn_showAnswer = $parser->parse_message($get_answer['sn_answer'], $options);
            eval("\$socialnetwork_member_answerbit .= \"" . $templates->get('socialnetwork_member_answerbit') . "\";");
        }
        eval("\$socialnetwork_member_postbit .= \"" . $templates->get('socialnetwork_member_postbit') . "\";");
    }
}

function showFriends()
{
    global $db, $mybb, $templates, $lang, $socialnetwork_member_friends, $socialnetwork_member_friendsbit, $socialnetwork_member_friendsbitAsked, $socialnetwork_member_friendsAddDelete;

    $thisuser = intval($mybb->user['uid']);
    $usesSN = getSnUserInfo($thisuser);
    $allowed = 1;
    if ($usesSN['uid'] == "") {
        $allowed = 0;
    }

    $activepage = intval($mybb->input['uid']);
    $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
    $flagFriends = 0;
    //Query to check if already friends
    $friendquery = $db->simple_select("sn_friends", "*", "(sn_uid = '$thisuser' AND sn_friendwith = '$activepage') OR (sn_uid = '$activepage' AND sn_friendwith = '$thisuser') ");
    $friendValue = "plus";
    if ($db->num_rows($friendquery) > 0) {
        $friendValue = "minus";
        $flagFriends = "," . $thisuser . "," . $activepage . ",";
    }

    //Check if this user has allready asked for friendship
    $friendqueryAsked = $db->simple_select("sn_friends", "*", "(sn_uid = '$activepage' AND sn_friendwith = '$thisuser') AND sn_accepted=0");

    //Get all Friends of this active Page
    $queryFriends = $db->simple_select("sn_friends", "*", "sn_uid = $activepage OR (sn_friendwith = $activepage AND sn_accepted = 0)");

    // $socialnetwork_member_friendsbitToAccept = "";
    $titcnt = 0;
    $titAccCnt = 0;
    while ($get_friend = $db->fetch_array($queryFriends)) {
        $friend = $get_friend['sn_friendwith'];
        //Get Data of friend
        $frienddata = get_user($friend);
        $frienddataSN = getSnUserInfo($friend);
        if ($frienddataSN['sn_avatar'] == "") {
            $friendava = $defaultava;
        } else {
            $friendava = $frienddataSN['sn_avatar'];
        }
        if ($frienddataSN['sn_nickname'] == "") {
            $friendname = "<a href=\"" . get_profile_link($friend) . "&area=socialnetwork\">" . $frienddata['username'] . "</a>";
        } else {
            $friendname = "<a href=\"" . get_profile_link($friend) . "&area=socialnetwork\">" . $frienddataSN['sn_nickname'] . "</a>";
        }

        if ($thisuser == $activepage) {
            $frienddelete = "<a href=\"member.php?action=profile&uid=" . $activepage . "&area=socialnetwork&friend=minus&friendid=" . $friend . "\">" . $lang->socialnetwork_member_delete . "</a>";
        }

        if ($get_friend['sn_accepted'] != 0) {
            eval("\$socialnetwork_member_friendsbit .= \"" . $templates->get('socialnetwork_member_friendsbit') . "\";");
        } else if ($thisuser == $activepage && $get_friend['sn_uid'] == $activepage) {
            $titAccCnt++;
            if ($titAccCnt == 1) $socialnetwork_member_friendsbitToAccept = $lang->socialnetwork_member_openRequestFriendTit;

            eval("\$socialnetwork_member_friendsbitToAccept .= \"" . $templates->get('socialnetwork_member_friendsbitToAccept') . "\";");
        } else if ($thisuser == $activepage && $get_friend['sn_friendwith'] == $activepage) {
            $titcnt++;
            if ($titcnt == 1) {
                $socialnetwork_member_friendsbitAsked = $lang->socialnetwork_member_openRequestFriendAskedTit;
            };
            $askedFriend = get_user($get_friend['sn_uid']);
            $askedFriendSN = getSnUserInfo($get_friend['sn_uid']);
            if ($askedFriendSN['sn_avatar'] == "") {
                $friendava = $defaultava;
            } else {
                $friendava = $askedFriendSN['sn_avatar'];
            }
            if ($askedFriendSN['sn_nickname'] == "") {
                $friendname = "<a href=\"" . get_profile_link($get_friend['sn_uid']) . "&area=socialnetwork\">" . $askedFriend['username'] . "</a>";
            } else {
                $friendname = "<a href=\"" . get_profile_link($get_friend['sn_uid']) . "&area=socialnetwork\">" . $askedFriendSN['sn_nickname'] . "</a>";
            }
            eval("\$socialnetwork_member_friendsbitAsked .= \"" . $templates->get('socialnetwork_member_friendsbitAsked') . "\";");
        }
    }

    if ($thisuser != $activepage) {
        $socialnetwork_member_friendsAddDelete = "
        <a href=\"member.php?action=profile&uid=" . $activepage . "&area=socialnetwork&friend=" . $friendValue . "&friendid=" . $activepage . "\"><span class=\"fas fa-user-" . $friendValue . "\" aria-label=\"" . $friendValue . "\" id=\"friendAddRemove\"></span></a>";
        if ($db->num_rows($friendqueryAsked) > 0) {
            $socialnetwork_member_friendsAddDelete = $lang->socialnetwork_member_openRequestFriendAskedOtherPage;
        }
    } else {
        if ($db->num_rows($friendqueryAsked) > 0) {
            $socialnetwork_member_friendsAddDelete = $lang->socialnetwork_member_openRequestFriendAskedOwnPage;
        }
    }

    if ($mybb->input['friend'] == "plus") {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            addFriend($friendid, $thisuser);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    if ($mybb->input['friend'] == "minus" && ($activepage == intval($mybb->input['friendid']))) {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            deleteFriend($friendid, $thisuser, $flagFriends);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    if ($mybb->input['friend'] == "accept" && ($thisuser == $activepage)) {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            echo "userid" . $friendid . "  this: " . $thisuser;
            acceptFriend($friendid, $thisuser);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    if ($mybb->input['friend'] == "deny" && ($thisuser == $activepage)) {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            denyFriend($friendid, $thisuser);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    eval("\$socialnetwork_member_friends .= \"" . $templates->get('socialnetwork_member_friends') . "\";");
}

/**
 * Add Friend
 * @param int $userid Id of User who should be added
 * @param int $thisuser who sent the request
 */
function addFriend($userid, $thisuser)
{
    global $db, $mybb;


    $insert_array = array(
        "sn_uid" => $userid,
        "sn_friendwith" => $thisuser,
        "sn_accepted" => 0,
    );
    $db->insert_query("sn_friends", $insert_array);

    //we don't want the param stuff in URI after friend is added
    redirect('member.php?action=profile&uid=' . $userid . '&area=socialnetwork');
}
/**
 * Delete Friend 
 * @param int $userid Id of User who should be deleted
 * @param int $thisuser which user ist currently online
 * @param string $flagFriends for checking if they're friends and friend (saving id of current user and activepage - if they are friends else it's 0) can delete friendship
 */
function deleteFriend($userid, $thisuser, $flagFriends)
{
    global $db, $mybb;
    // echo "falsch". $flagFriends;
    //$userid == $this user -> user is on is own page and is allowed to delete this friendship
    //User is not on his own page, but they are friends and user wants to delete this friendship, so they have to be in the flag
    if ($userid == $thisuser || (strpos($flagFriends, (string)$userid) !== false && strpos($flagFriends, (string)$thisuser) !== false)) {
        $db->delete_query("sn_friends", "sn_uid = $userid AND sn_friendwith = $thisuser");
        $db->delete_query("sn_friends", "sn_uid = $thisuser AND sn_friendwith = $userid");
    }
    $goto = $userid;
    if (intval($mybb->input['uid']) == $thisuser) {
        $goto = $thisuser;
    }
    //we don't want the param stuff in URI after friend is added
    redirect('member.php?action=profile&uid=' . $goto . '&area=socialnetwork');
}
/** 
 * Accept Friend
 * @param int userid Id of User who should be accepted
 * @param int $thisuser who is online?
 */
function acceptFriend($userid, $thisuser)
{
    global $db;
    echo "userid" . $userid . "  this: " . $thisuser;
    $update_array = array(
        "sn_accepted" => 1
    );
    $insert_array = array(
        "sn_uid" => $userid,
        "sn_friendwith" => $thisuser,
        "sn_accepted" => 1
    );

    $db->update_query("sn_friends", $update_array, "sn_uid = $thisuser AND sn_friendwith = $userid");
    $db->insert_query("sn_friends", $insert_array);
    redirect('member.php?action=profile&uid=' . $thisuser . '&area=socialnetwork');
}
/** 
 * Accept Friend
 * @param int userid Id of User who should be dnied
 * @param int $thisuser who is online?
 */
function denyFriend($userid, $thisuser)
{
    global $db, $mybb;
    $db->delete_query("sn_friends", "sn_uid = $userid AND sn_friendwith = $thisuser");
    $db->delete_query("sn_friends", "sn_uid = $thisuser AND sn_friendwith = $userid");
    $goto = intval($mybb->input['uid']);
    redirect('member.php?action=profile&uid=' .  $goto . '&area=socialnetwork');
}

function mentionUser($message, $thispage, $postid, $aid)
{
    global $db, $mybb;
    $users = array();

    //get all usernames and id
    $queryUsernames = $db->simple_select("users", "uid, username");
    $querySocialnames = $db->simple_select("sn_users", "uid, sn_nickname");

    while ($saveUser = $db->fetch_array($queryUsernames)) {
        // $autor= intval($uid['uid']);
        $users[$saveUser['username']] = $saveUser['uid'];
    }
    while ($saveUserSN = $db->fetch_array($querySocialnames)) {
        // $autor= intval($uid['uid']);
        if ($saveUserSN['sn_nickname'] != "") {
            $users[$saveUserSN['sn_nickname']] = $saveUserSN['uid'];
        }
    }

    foreach ($users as $name => $men_uid) {
        $searchstring = "/@" . $name . "/";
        if (preg_match($searchstring, $message)) {
            checkMentions("mention", $thispage, $men_uid, $postid, $aid);
        }
    }
}

/**
 * ALERT FUNCTION to handle PMs an MyAlerts
 * do we want to send an Alert/PN ? 
 * @param string $type = which kind of alert, do we have to handle
 * @param $pageid  id of page
 * @param int $uid   user who is online or who hast tob be informed
 * @param int $pid = postid if post
 * @param int $answerid if we're working with an answer, otherwise it's 0
 */
function checkMentions($type, $pageid, $uid, $pid, $aid)
{
    global $db, $mybb, $lang;
    $lang->load("socialnetwork");
    //admin settings, PM or Alert? Or Both?
    $pm = $mybb->settings['socialnetwork_alertpn'];
    $alert = $mybb->settings['socialnetwork_alertAlert'];
    //some stuff wie need 
    $thisuser = intval($mybb->user['uid']);
    $thisusername = $mybb->user['username'];
    //data of the user whos uid is given //sometimes this user, sometimes other, depends on type
    $uidData = get_user($uid);

    //settings of user, for PM handle of user from active page
    $alert_post = $db->fetch_field($db->simple_select("sn_users", "sn_alertPost", "uid = '$pageid'"), "sn_alertPost");

    //stuff for PM
    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();

    //Mentions
    //what kind of Mention do we have 
    switch ($type) {
        case "post":
            //check if the user wants an alert, handle in case of PM 
            if ($pm == 1 && $alert_post == 1 && $pageid != $uid) {
                $socialnetwork_pm_post = $lang->socialnetwork_pm_post;
                //set language variables
                $lang->socialnetwork_pm_post = $lang->sprintf($socialnetwork_pm_post, $uidData['username'], $pageid, $pid);
                $pm = array(
                    "subject" => $lang->socialnetwork_pm_postSubject,
                    "message" =>  $lang->socialnetwork_pm_post,
                    "fromid" =>  $uid,
                    "toid" => $pageid
                );
                $pmhandler->set_data($pm);
                if (!$pmhandler->validate_pm()) {
                    $pm_errors = $pmhandler->get_friendly_errors();
                    return $pm_errors;
                } else {
                    $pminfo = $pmhandler->insert_pm();
                }
            }
            //we are using myAlert
            if ($alert == 1) {
                //is My Alert really installed? 
                if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    //we get the infos of the Alert Typa (sn_Post)
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('sn_Post');
                    //Not null, the user wants an alert and the user is not on his own page.
                    if ($alertType != NULL && $alertType->getEnabled() && $pageid != $uid) {
                        //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$pageid, $alertType, (int)$id);
                        //some extra details
                        $alert->setExtraDetails([
                            'postid' => $pid,
                            'pageid' => $pageid,
                            'fromuser' => $uid
                        ]);
                        //add the alert
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }
            }

            break;
        case "answer":
            //get uid of post to which the current user has answered
            $posteruid  = $db->fetch_field($db->simple_select("sn_posts", "sn_uid", "sn_post_id = " .  $pid), "sn_uid");
            //do this user want to have an alert? 
            $posteruidAlert  = $db->fetch_field($db->simple_select("sn_users", "*", "uid = " . $posteruid), "sn_alertPost");
            //we're using PMs, the user want to have an alert, and the user is not answering on his own page
            if ($pm == 1 && $posteruidAlert == 1 && $pageid != $uid) {
                $socialnetwork_pm_answer = $lang->socialnetwork_pm_answer;
                //set the language variables
                $lang->socialnetwork_pm_answer = $lang->sprintf($socialnetwork_pm_answer, $uidData['username'], $pageid, $aid);
                $pm = array(
                    "subject" => $lang->socialnetwork_pm_answerSubject,
                    "message" => $lang->socialnetwork_pm_answer,
                    "fromid" => $uid,
                    "toid" => $posteruid
                );
                $pmhandler->set_data($pm);
                if (!$pmhandler->validate_pm()) {
                    $pm_errors = $pmhandler->get_friendly_errors();
                    return $pm_errors;
                } else {
                    $pminfo = $pmhandler->insert_pm();
                }
            }

            if ($alert == 1) {
                //The owner of page = page id
                echo "ansid ist" . $aid;
                if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('sn_Answer');
                    //Not null, the user wants an alert and the user is not on his own page.
                    if ($alertType != NULL && $alertType->getEnabled() && $pageid != $uid) {
                        //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$pageid, $alertType, (int)$aid);
                        //some extra details
                        $alert->setExtraDetails([
                            'answerid' => $aid,
                            'pageid' => $pageid,
                            'fromuser' => $uid
                        ]);
                        //add the alert
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                    //The Post is not from pageowner, someone has answer to it -> so we want the original poster to get an alert
                    if ($posteruid != $pageid && $posteruid != $uid) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$posteruid, $alertType, (int)$aid);
                        $alert->setExtraDetails([
                            'answerid' => $aid,
                            'pageid' => $pageid,
                            'fromuser' => $uid,
                            'postid' => $pid //$answertopostid?
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }
            }
            break;
        case "like":
            // checkMentions("like", $thispage, $thisuser, $sn_postid, $sn_ansid);
            if ($sn_postid != 0 && $sn_ansid == 0) {
                //POST
            } else {
                //ANTWORT
            }

            break;
        case "friend":
            echo "i ist gleich 2";
            break;
        case "mention":
            if ($aid != 0) {
                $pid = "ans_" . $aid;
            } else $pid = $pid;
            $snData = getSnUserInfo($uid);
            if ($pm == 1 && $snData['sn_alertLike'] == 1) {
                $socialnetwork_pm_mention = $lang->socialnetwork_pm_mention;
                $lang->socialnetwork_pm_mention = $lang->sprintf($socialnetwork_pm_mention, $thisusername, $pageid, $pid);

                $pm = array(
                    "subject" => $lang->socialnetwork_pm_mentionSubject,
                    "message" => $lang->socialnetwork_pm_mention,
                    "fromid" => $thisuser,
                    "toid" => $uid
                );
                $pmhandler->set_data($pm);
                if (!$pmhandler->validate_pm()) {
                    $pm_errors = $pmhandler->get_friendly_errors();
                    return $pm_errors;
                } else {
                    $pminfo = $pmhandler->insert_pm();
                }
            }

            if ($alert == 1) {
                //The owner of page = page id
                if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('sn_Mention');
                    //Not null, the user wants an alert and the user is not on his own page.
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$uid, $alertType, (int)$postid);
                        //some extra details
                        $alert->setExtraDetails([
                            'postid' => $pid,
                            'pageid' => $pageid,
                            'fromuid' => $thisusername,
                            'toinform' => $uid
                        ]);
                        //add the alert
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }
            }
            break;
    }
}

/**
 * Save Post or Answer
 * @param int $id activepage (for post) or postid(for answer)
 * @param int $thisuser (who has sent the post)
 * @param string $date (choosen date and time)
 * @param string $message (content)
 * @param string $type (post or answer)
 */
function savingPostOrAnswer($id, $thisuser, $date, $message, $type)
{
    global $db;
    switch ($type) {
        case "sn_posts":
            $insert_array = array(
                "sn_pageid" =>  $id,
                "sn_uid" => $thisuser,
                "sn_date" => $date,
                "sn_social_post" => $message,
            );
            break;
        case "sn_answers":
            $insert_array = array(
                "sn_post_id" =>  $id,
                "sn_uid" => $thisuser,
                "sn_date" => $date,
                "sn_answer" => $message,
            );
            break;
    }
    $db->insert_query($type, $insert_array);
}
/**
 * Uodate  Post or Answer
 * @param int $id of post or answer
 * @param string $date (new)date and time of post or answer
 * @param string $message (new)message (content)
 * @param string $type (post or answer)
 */
function updatePostOrAnswer($id, $date, $message, $type)
{
    global $db;
    switch ($type) {
        case "sn_posts":
            $option = "sn_post_id";
            $update_array = array(
                "sn_date" => $date,
                "sn_social_post" => $message,
            );
            break;
        case "sn_answers":
            $option = "sn_aid";
            $update_array = array(
                "sn_date" => $date,
                "sn_answer" => $message,
            );
            break;
    }
    $db->update_query($type, $update_array, $option . "=" . $id);
}
/**
 * Like a post/or Answer
 * @param int $activepage
 * @param int $sn_postid
 * @param int $sn_ansid
 * @param int $sn_uid
 */
function like($activepage, $sn_postid, $sn_ansid, $sn_uid)
{
    global $db;

    $isokay = true;
    if ($sn_postid != 0) {
        //we need to check, if the user allready liked the post or the answer 
        $queryCheck = $db->simple_select("sn_likes", "*", "sn_postid = $sn_postid AND sn_uid = $sn_uid ");
        if (!empty($db->fetch_array($queryCheck))) {
            $isokay = false;
            redirect('member.php?action=profile&uid=' . $activepage . '&area=socialnetwork');
        }
    }
    if ($sn_ansid != 0) {
        $queryCheck = $db->simple_select("sn_likes", "*", "sn_answerid = $sn_ansid AND sn_uid = $sn_uid ");
        if (!empty($db->fetch_array($queryCheck))) {
            $isokay = false;
            redirect('member.php?action=profile&uid=' . $activepage . '&area=socialnetwork');
        }
    }
    //we don't want user to like a post more than once!
    if ($isokay === true) {
        $insert_array = array(
            "sn_postid" => $sn_postid,
            "sn_answerid" => $sn_ansid,
            "sn_uid" => $sn_uid,
        );

        $db->insert_query("sn_likes", $insert_array);
        redirect('member.php?action=profile&uid=' . $activepage . '&area=socialnetwork');
    }
}
/**
 * Disike a post/or Answer
 * @param int $activepage
 * @param int $sn_postid
 * @param int $sn_ansid
 * @param int $sn_uid
 */
function dislike($activepage, $sn_postid, $sn_ansid, $sn_uid)
{
    global $db;
    //we're disliking a post
    if ($sn_postid != 0) {
        $db->delete_query("sn_likes", "sn_uid = $sn_uid AND sn_postid = $sn_postid");
        redirect('member.php?action=profile&uid=' . $activepage . '&area=socialnetwork');
    }
    //we're disliking an answer
    if ($sn_ansid != 0) {
        $db->delete_query("sn_likes", "sn_uid = $sn_uid AND sn_answerid = $sn_ansid");
        redirect('member.php?action=profile&uid=' . $activepage . '&area=socialnetwork');
    }
}

/**** Helper Functions  ******/

/** 
 * get own fields and return an array with cleaned names 
 *  @return array The created fields of admin
 * */
function getOwnFields()
{
    global $db;
    //get own fields
    $columns = $db->write_query("SHOW COLUMNS FROM " . TABLE_PREFIX . "sn_users WHERE field LIKE 'own_%'"); //
    $fields = array();
    //save them in an array and save a string - without the prefix own_
    while ($column = $db->fetch_array($columns)) {
        array_push($fields, str_replace('own_', '', $column['Field']));
    }
    return $fields;
}
/**
 * get sizes of Avatar and Title from settings of admin, return Array with sizes
 * @return array The users data
 * */
function get_avatit_size()
{
    global $mybb;
    $sizearray = array();
    $avasizearr = explode(',', $mybb->settings['socialnetwork_avasize']);
    $titlesizearr = explode(',', $mybb->settings['socialnetwork_titlesize']);
    array_push($sizearray, $avasizearr[0]); //index 0 = avabreite
    array_push($sizearray, $avasizearr[1]); //index 1 = avahöhe
    array_push($sizearray, $titlesizearr[0]); //index2 = titelbilbbreite
    array_push($sizearray, $titlesizearr[1]); //index3 = titelbildhöhe
    return $sizearray;
}
/**
 * get the infos of a user of social network
 * @param int $userid
 * @return array The users data
 */
function getSnUserInfo($userid)
{
    global $mybb, $db;
    $userArray = array();
    $userArray = ($db->fetch_array($db->simple_select("sn_users", "*", "uid = $userid", "limit 1")));
    return $userArray;
}

/*****
 * 
 * My Alert Integration
 * 
 * *** */
if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "socialnetwork_alert");
}
function socialnetwork_alert()
{
    global $mybb, $lang;
    $lang->load('socialnetwork');
    /**
     * We need our MyAlert Formatter
     * Alert Formater for sn_Post
     */
    class MybbStuff_MyAlerts_Formatter_SocialnetworkNewPostFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Build the output string tfor listing page and the popup.
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->socialnetwork_sn_Post,
                $outputAlert['from_user'],
                $alertContent['pageid'],
                $alertContent['postid'],
                $outputAlert['dateline']
            );
        }
        /**
         * Initialize the language, we need the variables $l['myalerts_setting_alertname'] for user cp! 
         * and if need initialize other stuff
         * @return void
         */
        public function init()
        {
            if (!$this->lang->socialnetwork) {
                $this->lang->load('socialnetwork');
            }
        }
        /**
         * We want to define where we want to link to. 
         * @param MybbStuff_MyAlerts_Entity_Alert $alert for which alert.
         * @return string return the link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent['pageid'] . '&area=socialnetwork#' . $alertContent['postid'] . '';
        }
    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkNewPostFormatter($mybb, $lang, 'sn_Post')
        );
    }
    /**
     * We need our MyAlert Formatter
     * Alert Formater for sn_Answer
     */
    class MybbStuff_MyAlerts_Formatter_SocialnetworkNewAnswerFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert2, array $outputAlert2)
        {
            $alertContent2 = $alert2->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->socialnetwork_sn_Answer,
                $outputAlert2['from_user'],
                $alertContent2['pageid'],
                $alertContent2['answerid'],
                $outputAlert2['dateline']
            );
        }
        public function init()
        {
            if (!$this->lang->socialnetwork) {
                $this->lang->load('socialnetwork');
            }
        }
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert2)
        {
            $alertContent2 = $alert2->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent2['pageid'] . '&area=socialnetwork#ans_' . $alertContent2['answerid'] . '';
        }
    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        if (!$formatterManagerAns) {
            $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManagerAns->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkNewAnswerFormatter($mybb, $lang, 'sn_Answer')
        );
    }

    /**
     * We need our MyAlert Formatter
     * Alert Formater for sn_Like
     */
    class MybbStuff_MyAlerts_Formatter_SocialnetworkMentionFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert3, array $outputAlert3)
        {
            $alertContent3 = $alert3->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->socialnetwork_sn_Mention,
                $alertContent3['fromuid'],
                $alertContent3['toinform'],
                $outputAlert3['dateline']
            );
        }
        public function init()
        {
            if (!$this->lang->socialnetwork) {
                $this->lang->load('socialnetwork');
            }
        }
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert3)
        {
            $alertContent3 = $alert3->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent3['pageid'] . '&area=socialnetwork#' . $alertContent3['postid'] . '';
        }
    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        if (!$formatterManagerAns) {
            echo "im alert";
            $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManagerAns->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkMentionFormatter($mybb, $lang, 'sn_Mention')
        );
    }
}
/** *****
 * INFINITE SCROLLING
 * This function could handle infinite scrolling(like facebook), you can use this instead of 'showPosts()' 
 * but beware, direct links from notifications may not be working, when post/answer isn't already loaded
 *
 ***** */
function showPostAjax()
{
    global  $activepage, $db, $lang, $mybb, $templates, $parser, $infinitescrolling, $socialnetwork_member_postbit, $socialnetwork_member_answerbit, $socialnetwork_member_postimg;
    //Parser options
    $options = array(
        "allow_html" => $mybb->settings['socialnetwork_html'],
        "allow_mycode" => $mybb->settings['socialnetwork_mybbcode'],
        "allow_imgcode" => $mybb->settings['socialnetwork_img'],
        "filter_badwords" => $mybb->settings['socialnetwork_badwords'],
        "nl2br" => 1,
        "allow_videocode" => $mybb->settings['socialnetwork_videos'],
    );

    $offset = 0;
    $no_of_records_per_page = 5;

    $activepage = intval($mybb->input['uid']);
    $thisUser = intval($mybb->user['uid']);
    $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
    $queryPosts = $db->simple_select("sn_posts", "*", "sn_pageid = $activepage", array(
        "order_by" => 'sn_date',
        "order_dir" => 'DESC',
        "limit start" => $offset,
        "limit" => $no_of_records_per_page
    ));
    $cnt_likes_post = "";

    $pageno = $_POST['pageno'];
    $no_of_records_per_page = 10;
    $offset = ($pageno - 1) * $no_of_records_per_page;

    while ($get_post = $db->fetch_array($queryPosts)) {
        $likevar = "like";
        $sn_like = $lang->socialnetwork_member_like;
        $infinitescrolling = '<span style="text-align:center; display:block;"><img id="loader" src="images/spinner.gif"></div>';

        //show the image beside the anwser form
        $sn_ansFormImg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$thisUser'"), "sn_avatar");
        if ($sn_ansFormImg == "") $sn_ansFormImg = $defaultava;
        //poster uid
        $postuser = intval($get_post['sn_uid']);

        //did the poster have a nickname if not take the username
        $name =  htmlspecialchars_uni($db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = '$postuser'"), "sn_nickname"));
        if ($name == "") {
            $name =  htmlspecialchars_uni($db->fetch_field($db->simple_select("users", "username", "uid = '$postuser'"), "username"));
        }
        //we want to link to the social page of the poster
        $sn_postname = '<a href="member.php?action=profile&uid=' . $postuser . '&area=socialnetwork">' . $name . '</a>';
        //the avatar
        $sn_postimg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$postuser'"), "sn_avatar");
        if ($sn_postimg == "") $sn_postimg = $defaultava;
        //handle of deleted users
        if ($get_post['sn_del_username'] != "") {
            $sn_postname =  htmlspecialchars_uni($get_post['sn_del_username']);
            if ($get_post['sn_del_nickname'] != "") {
                $sn_postname =  htmlspecialchars_uni($get_post['sn_del_nickname']);
            }
            $sn_postimg = $defaultava;
        }

        //the other informations of post
        $sn_date = date('d.m.y - H:i', strtotime($get_post['sn_date']));

        $sn_showPost = $parser->parse_message($get_post['sn_social_post'], $options);
        $sn_postid = intval($get_post['sn_post_id']);

        $sn_post_ed_del = "";
        //edit and delete
        if (($thisUser == $postuser) || ($mybb->usergroup['canmodcp'] == 1)) {
            $sn_date_date = date('Y-m-d', strtotime($get_post['sn_date']));
            $sn_date_time = date('H:i', strtotime($get_post['sn_date']));
            eval("\$sn_post_ed_del = \"" . $templates->get("socialnetwork_member_postedit") . "\";");
        }

        //we have to clear the variables first
        $socialnetwork_member_answerbit = "";
        $socialnetwork_member_postimg = "";
        //Get all likes
        $likeQuery = $db->simple_select("sn_likes", "*", "sn_postid = $sn_postid");
        while ($likesarray = $db->fetch_array($likeQuery)) {
            //do the user already like the post? -> then we want to show the dislike stuff
            if ($likesarray['sn_uid'] == $thisUser) {
                $likevar = "dislike";
                $sn_like = $lang->socialnetwork_member_dislike;
            }
        }
        //count likes
        $cnt_likes_post = $db->fetch_field($db->simple_select("sn_likes", "count(*) as cnt", "sn_postid = $sn_postid"), "cnt");

        //Do the user upload an image to the post?
        $postImg = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = $sn_postid"));
        if (!empty($postImg)) {
            eval("\$socialnetwork_member_postimg = \"" . $templates->get('socialnetwork_member_postimg') . "\";");
        }
        //variale to count the likes of an answer
        $cnt_likes_ans = "";
        //and here we get the answers for the actual post
        $queryAnswer = $db->simple_select("sn_answers", "*", "sn_post_id = $sn_postid", array(
            "order_by" => 'sn_date',
            "order_dir" => 'DESC'
        ));
        $sn_ans_ed_del = "";
        while ($get_answer = $db->fetch_array($queryAnswer)) {
            //Initial like stuff for answers
            $likevar_ans = "like";
            $sn_like_ans = $lang->socialnetwork_member_like;

            $ansid = intval($get_answer['sn_aid']);
            //count like of answers
            $cnt_likes_ans = $db->fetch_field($db->simple_select("sn_likes", "count(sn_postid) as cnt", "sn_answerid = $ansid"), "cnt");
            //all likes
            $likeQuery = $db->simple_select("sn_likes", "*", "sn_answerid = $ansid");
            while ($likesarray = $db->fetch_array($likeQuery)) {
                if ($likesarray['sn_uid'] == $thisUser) {
                    $likevar_ans = "dislike";
                    $sn_like_ans = $lang->socialnetwork_member_dislike;
                }
            }
            //uid of answer
            $sn_ansUser = intval($get_answer['sn_uid']);
            //avatar 
            $sn_anspostimg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$sn_ansUser'"), "sn_avatar");
            if ($sn_anspostimg == "") $sn_anspostimg = $defaultava;
            //name (nickname or username?)
            $ansname =  htmlspecialchars_uni($db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = '$sn_ansUser'"), "sn_nickname"));
            if ($ansname == "") $ansname =  htmlspecialchars_uni($db->fetch_field($db->simple_select("users", "username", "uid = '$sn_ansUser'"), "username"));
            $sn_ansname = '<a href="member.php?action=profile&uid=' . $sn_ansUser . '&area=socialnetwork">' . $ansname . '</a>';

            //handle of deleted user
            if ($get_answer['sn_del_username'] != "") {
                $sn_ansname =  htmlspecialchars_uni($get_answer['sn_del_username']);
                if ($get_answer['sn_del_nickname'] != "") {
                    $sn_ansname =  htmlspecialchars_uni($get_answer['sn_del_nickname']);
                }
                $sn_anspostimg = $defaultava;
            }
            $sn_ansdate = date('d.m.y - H:i', strtotime($get_answer['sn_date']));
            $sn_ans_ed_del = "";
            //edit and delete
            if (($thisUser == $sn_ansUser) || ($mybb->usergroup['canmodcp'] == 1)) {
                // eval("\$sn_post_ed_del = \"".$templates->get("socialnetwork_member_postedit")."\";");
                $ansdate = date('Y-m-d', strtotime($get_answer['sn_date']));
                $anstime = date('H:i', strtotime($get_answer['sn_date']));
                eval("\$sn_ans_ed_del = \"" . $templates->get("socialnetwork_member_answeredit") . "\";");
            }
            $sn_showAnswer = $parser->parse_message($get_answer['sn_answer'], $options);
            eval("\$socialnetwork_member_answerbit .= \"" . $templates->get('socialnetwork_member_answerbit') . "\";");
        }
        eval("\$socialnetwork_member_postbit .= \"" . $templates->get('socialnetwork_member_postbit') . "\";");
    }
}
