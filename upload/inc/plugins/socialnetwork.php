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
//error_reporting(E_ERROR | E_PARSE);
//ini_set('display_errors', true);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function socialnetwork_info()
{
    global $lang, $db, $plugins_cache, $mybb;
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
                                                        <img src=\"styles/default/images/icons/custom.png\" alt=\"\" style=\"margin-left: 10px;\" /><a href=\"" . $mybb->settings['bburl'] . "/social_saveold.php\" style=\"margin: 10px;\">" . $lang->socialnetwork_infoolddata . "</a><hr style=\"margin-bottom: 5px;\"></div>";
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
    global $db, $lang, $cache;
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
        `sn_alertFriendReq` TINYINT(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`uid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create table for posts
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_posts` (
		`sn_post_id` int(20) NOT NULL AUTO_INCREMENT,
        `sn_pageid` int(20) NOT NULL,
		`sn_uid` int(20) NOT NULL,
        `sn_date` datetime NOT NULL,
  		`sn_social_post` varchar(500) NOT NULL,
  		`sn_del_name` varchar(100) DEFAULT NUll,
	PRIMARY KEY (`sn_post_id`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create table for answers
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_answers` (
		`sn_aid` int(20) NOT NULL AUTO_INCREMENT,
		`sn_post_id` int(20) NOT NULL,
  		`sn_date` datetime NOT NULL,
  		`sn_uid` int(20) NOT NULL,
  		`sn_answer` varchar(500) NOT NULL,
  		`sn_del_name` varchar(100) DEFAULT NUll,
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
        `sn_width` int(11) NOT NULL,
        `sn_height` int(11) NOT NULL,
        `sn_uid` int(11) NOT NULL,
        `sn_postId` int(11) NOT NULL,
        `sn_type` varchar(11) NOT NULL,
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
            'title' => $lang->socialnetwork_settings_titlesize_tit,
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
        'socialnetwork_orderOffFields' => array(
            'title' => $lang->socialnetwork_settings_orderOffFields_tit,
            'description' => $lang->socialnetwork_settings_orderOffFields,
            'optionscode' => 'text',
            'value' => '', // Default
            'disporder' => 16
        ),
        'socialnetwork_scrolling' => array(
            'title' => $lang->socialnetwork_settings_scrolling_tit,
            'description' => $lang->socialnetwork_scrolling,
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 17
        ),
        'socialnetwork_recordsperpage' => array(
            'title' => $lang->socialnetwork_settings_recordsperpage_tit,
            'description' => $lang->socialnetwork_settings_recordsperpage,
            'optionscode' => 'text',
            'value' => '5', // Default
            'disporder' => 18
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
        'prefix' => 'socialnetwork',
        'title'  => $db->escape_string($lang->socialnetwork_tplgroup),
        'isdefault' => 1
    );
    $db->insert_query("templategroups", $templategrouparray);

    socialnetwork_addtemplates();
    socialnetwork_addstylesheets();
    $cache->update_usergroups();

    if (!is_writable(MYBB_ROOT . 'social/userimages/')) {
        @chmod(MYBB_ROOT . 'social/userimages/', 0755);
    }
}

function socialnetwork_activate()
{
    global $cache;
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    //add variables to member_profile to show link to social network
    find_replace_templatesets("member_profile", "#" . preg_quote('{$userstars}') . "#i", '{$userstars}{$sn_page_profil}');
    find_replace_templatesets('modcp_nav_users', '#' . preg_quote('{$nav_ipsearch}') . '#', '{$nav_ipsearch} {$socialnetwork_modcp_nav}');

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

        $alertTypeFriendReq = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertTypeFriendReq->setCanBeUserDisabled(true);
        $alertTypeFriendReq->setCode("sn_FriendRequest");
        $alertTypeFriendReq->setEnabled(true);
        $alertTypeManager->add($alertTypeFriendReq);
    }
    $cache->update_usergroups();
}

function socialnetwork_deactivate()
{
    //remove template variables, so that it isn't shown anymore
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$sn_page_profil}') . "#i", '');
    find_replace_templatesets('modcp_nav_users', '#' . preg_quote('{$socialnetwork_modcp_nav}') . '#', '');
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
        $alertTypeManager->deleteByCode('sn_FriendRequest');
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
    if ($db->table_exists("sn_imgs")) $db->drop_table("sn_imgs");
    //remove templates
    $db->delete_query("templates", "title LIKE 'socialnetwork_%'");
    $db->delete_query("templategroups", "prefix = 'Socialnetwork'");

    //remove settings
    $db->delete_query('settings', "name LIKE 'socialnetwork%_'");
    $db->delete_query('settinggroups', "name = 'socialnetwork'");

    //remove stylesheet
    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'socialnetwork.css'");
    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }

    //remove settings usergroups
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
            <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
            {$headerinclude}
        
        </head>
        <body>
        {$header}
        <div class="socialmain">
        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
            <tr>
            <td class="trow1">
            <div class="container">
                <!--<div class="sn_title_section">-->
                <div class="sn_titel" style="background:url({$sn_thispage[\\\'sn_userheader\\\']});height:{$sn_titlesizeheight};"></div>
                <div class="sn_profil" style="width:{$sn_avasizewidth};height:{$sn_avasizeheight};"><img src="{$sn_thispage[\\\'sn_avatar\\\']}" alt="profilbild" /></div>
                <div class="sn_username"><h1>{$sn_thispage[\\\'sn_nickname\\\']}</h1></div>
                <!--</div>-->
                <div class="sn_down_section">
                    <div class="sn_leftBox">
                        <div class="sn_memInfo">
                            {$logo}
                            {$socialnetwork_member_infobit}
                            {$socialnetwork_member_friendsAddDelete}
                        </div>
                        {$socialnetwork_member_friends}
                    </div>
                    <div class="sn_rightBox">
                        <div class="sn_rechts">
                        <fieldset>
                            <legend>Beitrag erstellen</legend>
                            <form enctype="multipart/form-data" name="picform" id="picform" method="post">
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
                                <input type="hidden" id="thispage" value="{$mybb->input[\\\'uid\\\']}">
                             {$infinitescrolling}
                    </div>
                </div>
            </div>				
            </td>
            </tr>
        </table>
        </div>
        <script src="social/js/jquery.inview.js"></script>
        <script src="social/js/script.js"></script>
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
            <div class="sn_postBox">
                <div class="sn_postimg">
                    <input type="hidden"  value="{$postuser}" name="author" />
                    <a id="{$sn_postid}"></a>
                    <img class="sn_postProfilbild" src="{$sn_postimg}" alt="" />
                </div>
                <div class="sn_post">
                    <span class="sn_postName">{$sn_postname}</span>
                    <span class="sn_postDate">{$sn_date}</span>
                    <span class="sn_edit">{$sn_post_ed_del}</span>
                    <div class="sn_socialPost" id="p{$sn_postid}">{$sn_showPost}</div>
                    {$socialnetwork_member_postimg}
                    <div class="sn_likes">
                    Gefällt {$cnt_likes_post} Mal <a href="member.php?action=profile&uid={$thispage}&area=socialnetwork&like={$likevar}&postid={$sn_postid}&ansid=0">{$sn_like}</a>
    
                        </div>
                    {$socialnetwork_member_answerbit}
                        <div class="sn_answer_form">
                        <form method="post" enctype="multipart/form-data" name="picform" id="picform" >
                        <input type="hidden"  value="{$sn_postid}" name="postid" />
                        <img class="sn_answerFormProfilbild" src="{$sn_ansFormImg}" alt="" />
                        <input type="date" value="2017-08-01" name="sn_ansDatum" /> <input type="time" name="sn_ansUhrzeit" value="12:00" /><br />
                        <textarea id="sn_answer" name="sn_answer" rows="1" cols="60"></textarea><br />
                        <input type="file" name="uploadImg" size="60" maxlength="255"><br />
                        <input class="sn_send" type="submit" name="sendAnswer" value="senden">
                        </form>
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
        <a href="#popinfo{$postImgId}"><img src="social/userimages/{$postImgFilename}" style="max-width:98%; max-height:300px;" /></a>
            {$manage_img}
        </div>
        <div id="popinfo{$postImgId}" class="infopop">
          <div class="pop"><img src="social/userimages/{$postImgFilename}" style="max-width:100%; max-height:100%;" /></div><a href="#closepop" class="closepop"></a>
        </div>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[3] = array(
        "title" => 'socialnetwork_member_postedit',
        "template" => '
        <button class="editDelete" name="editpost" onclick="change({$sn_postid},\\\'{$sn_date_date}\\\',\\\'{$sn_date_time}\\\')" ><i class="fas fa-pen"></i></button>
        <a href="member.php?action=profile&uid={$thispage}&area=socialnetwork&postdelete={$sn_postid}" class="editDelete" ><i class="fas fa-trash"></i></a>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[4] = array(
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
{$socialnetwork_ucp_pmAlert}
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
		<input type="submit" value="{$lang->socialnetwork_save}" name="{$lang->socialnetwork_save}" class="button" />
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
    $template[5] = array(
        "title" => 'socialnetwork_ucp_nav',
        "template" => '<tr><td class="trow1 smalltext"><a href="usercp.php?action=socialnetwork">Soziales Netzwerk</a></td></tr>',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[6] = array(
        "title" => 'socialnetwork_modcp_singleuser',
        "template" => '
        <tr>
        <td class="{$altbg}">
            <a href="{$user[\\\'editsnlink\\\']}" title="{$lang->socialnetwork_modcp_edit}">{$userdata[\\\'username\\\']}</a>
        </td>
        <td class="{$altbg}" align="left">
            <a href="{$user[\\\'viewsnlink\\\']}">{$lang->socialnetwork_modcp_view}</a>
        </td>
        <td class="{$altbg}" align="center">
            <a href="{$user[\\\'editsnlink\\\']}">{$lang->socialnetwork_modcp_edit}</a>
        </td>
    </tr>
        ',
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
        "title" => 'socialnetwork_member_friendsbitToAccept',
        "template" => '
    <div class="sn_friend">
    <img src="{$friendava}" width="35px"/>  {$friendname} 
    <a href="member.php?action=profile&uid={$thispage}&area=socialnetwork&friend=accept&friendid={$friend}"><span class="fas fa-user-check" aria-label="accept"></span></a>
    <a href="member.php?action=profile&uid={$thispage}&area=socialnetwork&friend=deny&friendid={$friend}"><span class="fas fa-user-times" aria-label="deny"></span></a></div>
    ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[10] = array(
        "title" => 'socialnetwork_member_friendsbitAsked',
        "template" => '
        <div class="sn_friend">
        <img src="{$friendava}" width="35px"/>  {$friendname} 
        </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[11] = array(
        "title" => 'socialnetwork_member_friendsbit',
        "template" => '
        <div class="sn_friend"><img src="{$friendava}" width="35px"/> {$friendname} {$frienddelete}</div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[12] = array(
        "title" => 'socialnetwork_member_friends',
        "template" => '<div class="sn_links">
        <h1 class="friends">Friends</h1>
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
    $template[13] = array(
        "title" => 'socialnetwork_member_answeredit',
        "template" => '
        <button class="editDelete" name="editans" onclick="changeAns({$ansid},\\\'{$ansdate}\\\',\\\'{$anstime}\\\')"><i class="fas fa-pen"></i></button>
        <a href="member.php?action=profile&uid={$thispage}&area=socialnetwork&ansdelete={$ansid}" class="editDelete" ><i class="fas fa-trash"></i></a>
        <button class="editDelete" name="editans" onclick=""></button>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[14] = array(
        "title" => 'socialnetwork_member_answerbit',
        "template" => '<div class="sn_answer">
        <a id="ans_{$ansid}"></a>
        <input type="hidden" id="ans_{$ansid}" value="{$ansid}" name="ansid" />
        <img class="sn_ansProfilbild" src="{$sn_anspostimg}" alt="" />
        <span class="sn_ansName">{$sn_ansname}</span>
        <span class="sn_ansDate">{$sn_ansdate}</span>
        <span class="sn_edit">{$sn_ans_ed_del}</span>
        <div class="sn_socialAnswer" id="a{$ansid}">{$sn_showAnswer}</div>
        {$socialnetwork_member_postimg_ans}
    </div>
    <div class="sn_likes">
        Gefällt {$cnt_likes_ans} Mal <a
            href="member.php?action=profile&uid={$thispage}&area=socialnetwork&like={$likevar_ans}&postid=0&ansid={$ansid}">{$sn_like_ans}</a>
    </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[15] = array(
        "title" => 'socialnetwork_ucp_pmAlert',
        "template" => '
        <fieldset>
        <legend>Benachrichtigungseinstellungen</legend>
        <input type="checkbox" name="alertPost" {$sn_postcheck}> {$lang->socialnetwork_ucp_alertPost}<br>
        <input type="checkbox" name="alertLike" {$sn_likecheck}> {$lang->socialnetwork_ucp_alertLike}<br>
        <input type="checkbox" name="alertFriend" {$sn_friendcheck}> {$lang->socialnetwork_ucp_alertFriend}<br>
        <input type="checkbox" name="alertMention" {$sn_mentioncheck}> {$lang->socialnetwork_ucp_alertMention}</br>
        <input type="checkbox" name="alertFriendReq" {$sn_friendReqcheck}> {$lang->socialnetwork_ucp_alertFriendReq}</br>
        </fieldset>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[16] = array(
        "title" => 'socialnetwork_modcp_nav',
        "template" => '
        <tr><td class="trow1 smalltext"><a href="modcp.php?action=socialnetwork" class="modcp_nav_item modcp_nav_editprofile">{$lang->socialnetwork_modcp_nav}</a></td></tr>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[17] = array(
        "title" => 'socialnetwork_misc_answerbit',
        "template" => '<div class="sn_answer">
        <a id="ans_{$ansid}"></a>
            <input type="hidden" id="ans_{$ansid}" value="{$ansid}" name="ansid" />
            <img class="sn_ansProfilbild" src="{$sn_anspostimg}" alt="" />
            <span class="sn_ansName">{$sn_ansname}</span>
            <span class="sn_ansDate">{$sn_ansdate}</span>
            <div class="sn_socialAnswer" id="a{$ansid}">{$sn_showAnswer}</div>
			{$socialnetwork_misc_postimg_ans}
        </div>
        <div class="sn_likes">
            Gefällt {$cnt_likes_ans} Mal <a href="misc.php?action=sn_newsfeedAll&like={$likevar_ans}&postid=0&ansid={$ansid}">{$sn_like_ans}</a>
        </div>
        
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[18] = array(
        "title" => 'socialnetwork_misc_main',
        "template" => '
        <html>
        <head>
                <title>{$lang->socialnetwork_view_newsfeedAll}</title>
            <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
            {$headerinclude}
        
        </head>
        <body>
        {$header}
        <div class="socialmain">
        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
            <tr>
            <td class="trow1">
            <div class="container newspage">
                <div class="sn_down_section">	
                            <div class="newsfeed_pages">
                                {$multipage}
                            </div>
                            <div class="newsfeed_links">
                                <h1> {$newsfeed_links}</h1>
                            </div>
                    <div class="sn_rightBox">
        
                        {$socialnetwork_misc_postbit}
                                <div  id="posts" style="width:100%">
                                </div>
                                <input type="hidden" id="page" value="1">
                                <input type="hidden" id="thispage" value="{$mybb->input[\\\'uid\\\']}">
                                {$multipage}
                    </div>
                </div>
            </div>				
            </td>
            </tr>
        </table>
        </div>
        <script src="social/js/jquery.inview.js"></script>
        <script src="social/js/script.js"></script>
        {$footer}
        </body>
        </html>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[19] = array(
        "title" => 'socialnetwork_misc_postbit',
        "template" => '
        <div class="sn_rechts">
        <fieldset>
            <div class="sn_postBox">
                <div class="sn_postimg">
                    <input type="hidden"  value="{$postuser}" name="author" />
                    <a id="{$sn_postid}"></a>
                    <img class="sn_postProfilbild" src="{$sn_postimg}" alt="" />
                    <a id="{$sn_postid}" href="{$posturl}" class="gotolink"><span class="fas fa-arrow-right"></i></a>
    
                </div>
                <div class="sn_post">
                    <span class="sn_postName">{$sn_postname}</span>
                    <span class="sn_postDate">{$sn_date}</span>
                    
                    <div class="sn_socialPost" id="p{$sn_postid}">{$sn_showPost}</div>
                    {$socialnetwork_misc_postimg}
                    <div class="sn_likes">
                Gefällt {$cnt_likes_post} Mal <a href="misc.php?action=sn_newsfeedAll&like={$likevar}&postid={$sn_postid}&ansid=0">{$sn_like}</a>
    
                        </div>
                                {$socialnetwork_misc_answerbit}
    
                        <div class="sn_answer_form">
                        <form method="post" enctype="multipart/form-data" name="picform" id="picform" >
                        <input type="hidden"  value="{$sn_postid}" name="postid" />
                        <img class="sn_answerFormProfilbild" src="{$sn_ansFormImg}" alt="" />
                        <input type="date" value="2017-08-01" name="sn_ansDatum" /> <input type="time" name="sn_ansUhrzeit" value="12:00" /><br />
                        <textarea id="sn_answer" name="sn_answer" rows="1" cols="60"></textarea><br />
                        <input type="file" name="uploadImg" size="60" maxlength="255"><br />
                        <input class="sn_send" type="submit" name="sendAnswer" value="senden">
                        </form>
                        </div>
                </div>
            </div>
        </fieldset>
    </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[20] = array(
        "title" => 'socialnetwork_misc_postimg',
        "template" => '
        <div class="sn_img">
        <a href="#popinfo{$postImgId}"><img src="social/userimages/{$postImgFilename}" style="max-width:98%; max-height:300px;" /></a>
        </div>
        <div id="popinfo{$postImgId}" class="infopop">
          <div class="pop"><img src="social/userimages/{$postImgFilename}" style="max-width:100%; max-height:100%;" /></div><a href="#closepop" class="closepop"></a>
        </div>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );

    $template[21] = array(
        "title" => 'socialnetwork_modcp_main',
        "template" => '
        <html>
        <head>
            <title>{$lang->socialnetwork_modcp_tit} - {$mybb->settings[\\\'bbname\\\']}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
            
            <table width="100%" border="0" align="center">
                <tr>
                        {$modcp_nav}
         
                    <td valign="top" colspan="2">
                        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
                            <tr>
                                <td class="thead" colspan="3"><strong>{$lang->socialnetwork_modcp_tit}</strong></td>
                            </tr>
                            <td valign="top" align="left"colspan="3">
                        {$multipage}
                    </td>
                            <tr>
                                <td class="tcat"><strong>{$lang->username}</strong></td>
                                <td class="tcat" colspan="3" align="center"><strong>{$lang->action}</strong></td>
                            </tr>
                            {$socialnetwork_modcp_singleuser}
                        </table>
                        {$multipage}
                        <input type="hidden" id="page" value="1">
                    </td>
                </tr>
            </table>
            {$footer}
        </body>
    </html>
        ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[22] = array(
        "title" => 'socialnetwork_modcp_modify',
        "template" => '
        <html>
        <head>
            <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->socialnetwork_modcp_edittit}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
    <table width="100%" border="0" align="center">
        <tr>
        {$modcp_nav}
        <td valign="top">
        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
            <tr>
            <td class="thead" colspan="2"><strong>{$lang->socialnetwork_modcp_edittit}</strong></td>
            </tr>
            <tr>
            <td class="trow2">
                
                <div class="modcp_social">
                    <form method="post" action="modcp.php">
                {$socialnetwork_ucp_pmAlert}						
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
                        <div align="center">
             <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
            <input type="hidden" name="action" value="editsn_do" />
                            <input type="hidden" name="uid" value="{$uid}" />
            <input type="submit" value="{$lang->socialnetwork_save}" name="{$lang->socialnetwork_save}" class="button" />
                
            </div>		
            </form>		
                </div>
            </td>
            </tr>
            </table>
            <br />
    
        </td>
        </tr>
        </table>
        
            {$footer}
        </body>
    </html>
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
        "stylesheet" =>    '/*sn main page*/

        /*be sure, that accountswitcher attached accounts are working*/
        ul.trow1 {
            z-index: 10;
        }
        
        .socialmain fieldset,
        .ucp_social fieldset {
            padding: 12px;
            border: 1px solid #ddd;
            margin: 0;
        }
        
        .socialmain textarea {
            background-color: #fff;
            color: #000;
        }
        
        .socialmain button {
            background: none;
        }
        
        .socialmain legend {
            width: auto;
            display: block;
            max-width: 100%;
            padding: 0;
            margin-bottom: .5rem;
            font-size: 1.5rem;
            line-height: inherit;
            color: inherit;
            white-space: normal;
        }
        
        .socialmain .tborder {
            border: 0px;
            border-radius: 8px;
        }
        
        .socialmain .trow1 {
            background: #f5f5f5;
        }
        
        input.sn_send {
            margin-top: 3px;
            padding: 4px;
            padding-left: 10px;
            padding-right: 10px;
            margin-left: 5px;
            border-radius: 9px;
            border: 0;
        }
        
        
        /*title section*/
        .sn_titel {
            width: 100%;
            border: 0px #b1b1b1 solid;
            background-repeat: no-repeat !important;
            background-position: center 0px !important;
        }
        
        .sn_profil {
            background-color: #b1b1b1;
            margin-left: 70px;
            margin-top: -100px;
            margin-right: 10px;
            border-radius: 8px;
            float: left;
        }
        
        .sn_profil img {
            padding: 5px;
        }
        
        .sn_username {
            padding-left: 10px;
        }
        
        .sn_down_section {
            display: flex;
            flex-wrap: wrap;
        }
        
        .sn_logo{
            margin:auto;
            text-align:center;
        }
        
        /*info and friendsection*/
        .sn_leftBox {
            width: 30%;
        }
        
        .sn_memInfo {
            background-color: #b1b1b1;
            margin: 10px;
            padding: 10px;
            font-size: 12px;
            height: min-content;
            border-radius: 8px;
        }
        
        .sn_memInfo img {
            display: block;
            margin: auto;
            padding-top: 10px;
        }
        
        sn_tit {
            font-weight: bold;
        }
        
        .sn_links {
            background-color: #b1b1b1;
            margin: 10px;
            padding: 10px;
            font-size: 12px;
            height: min-content;
            border-radius: 8px;
        }
        
        input.editDelete {
            border: none;
            background: none;
            font-size: 0.8em;
            padding: 0px;
        }
        
        /*friendbox*/
        h1.friends {
            margin: auto;
            text-align: center;
            font-size: 2.0em;
        }
        
        .sn_friend {
            padding: 5px;
            display: -webkit-flex;
            display: flex;
            -webkit-align-items: center;
            align-items: center;
        }
        
        .sn_friend a {
            padding-left: 5px;
        }
        
        span.allreadyAsked {
            display: block;
            text-align: center;
            padding: 10px;
        }
        
        span#friendAddRemove {
            display: block;
            text-align: center;
            font-size: 2em;
            padding: 10px;
        }
        
        
        /*post view*/
        .sn_postBox {
            display: flex;
        }
        
        .sn_rightBox {
            margin: auto;
            width: 70%;
        }
        
        .sn_post {
            padding-left: 10px;
        }
        
        .sn_rechts {
            background-color: #b1b1b1;
            margin: 10px;
            padding: 10px;
            border-radius: 8px;
        }
        
        .sn_postProfilbild {
            border-radius: 8px;
            width: 50px;
            -webkit-border-radius: 100%;
            -moz-border-radius: 100%;
        }
        
        .sn_likes {
            text-align: right;
            border-bottom: 1px solid #ddd;
            font-size: 0.8em;
            margin-top: -20px;
            padding-bottom: 6px;
        }
        
        .sn_likes i.fas.fa-heart,
        .sn_likes i.far.fa-heart {
            font-size: 1.5em;
        }
        
        .editDelete {
            font-size: 0.8em;
            background: none;
            border: 0;
            padding: 0;
        }
        
        a.editDelete {
            -webkit-appearance: button;
            -moz-appearance: button;
            appearance: button;
            text-decoration: none;
            color: initial;
        }
        
        /*image pop up*/
        .infopop {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: hsla(0, 0%, 0%, 0.5);
            z-index: 1;
            opacity: 0;
            -webkit-transition: .5s ease-in-out;
            -moz-transition: .5s ease-in-out;
            transition: .5s ease-in-out;
            pointer-events: none;
        }
        
        .infopop:target {
            opacity: 1;
            pointer-events: auto;
        }
        
        .infopop>.pop {
            background: #aaaaaa;
            margin: 10% auto;
            padding: 10px;
            width: fit-content;
            z-index: 3;
        }
        
        .closepop {
            position: absolute;
            right: -5px;
            top: -5px;
            width: 100%;
            height: 100%;
            z-index: 2;
        }
        
        /* anworten */
        .sn_answer {
            margin: 11px 0px 10px 0px;
            padding-bottom: 5px;
            padding-left: 20px;
        }
        
        .sn_answerFormProfilbild,
        .sn_ansProfilbild {
            float: left;
            margin-right: 10px;
            width: 35px;
            -webkit-border-radius: 150%;
            -moz-border-radius: 100%;
        }
        
        .sn_answer_form {
            padding-top: 5px;
        }
        
        .sn_ansDate {
            font-size: 0.8em;
        }
        
        /*UCP*/
        
        .ucp_social legend {
            font-weight: bold;
        }
        
        .ucp_social label,
        .modcp_social label {
            display: block;
            width: 120px;
            float: left;
            clear: left;
        }
        
        .ucp_social input {
            margin: 5px;
        }
        
        .ucp_smallinfo {
            font-size: 0.7em;
        }
        
        .ucp_social legend {
            width: auto;
        }
        
        /*newsfeed*/
        .pagination .pages {
            padding: 3px;
        }
        
        .gotolink {
            display: block;
            text-align: right;
            margin-top: -10px;
        }
        
        .newsfeed_links h1 {
            text-align: right;
            font-size: 1.5em;
        }
        
        .sn_postName {
            display: block;
            font-weight: bold;
        }
        
        .sn_postDate {
            font-size: 0.8em;
        }
        
        .sn_answer_form {
            margin-top: 10px;
        }
        
        .sn_rechts hr {
            background-color: #ddd;
            color: #ddd;
            height: 1px;
            border: 0px;
        }
        ',
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
    $thisuser = intval($mybb->user['uid']);
    $pm = $mybb->settings['socialnetwork_alertpn'];

    if ($mybb->input['action'] == "socialnetwork") {
        add_breadcrumb($lang->nav_usercp, "usercp.php");
        add_breadcrumb($lang->socialnetwork_change, "usercp.php?action=socialnetwork");
        $linktosocial = '<span class="smalltext"><a href="member.php?action=profile&uid=' . $thisuser . '&area=socialnetwork">' . $lang->socialnetwork_ucp_link . '</a></span>';

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
        $get_input = $db->query("SELECT * FROM " . TABLE_PREFIX . "sn_users WHERE uid = " . $thisuser . "");
        while ($input = $db->fetch_array($get_input)) {
            $nickname = $input['sn_nickname'];
            $profilbild = $input['sn_avatar'];
            $titelbild = $input['sn_userheader'];
            $sn_alertPost = $input['sn_alertPost'];
            $sn_alertFriend = $input['sn_alertFriend'];
            $sn_alertLike = $input['sn_alertLike'];
            $sn_alertMention = $input['sn_alertMention'];
            $sn_alertfriendReqcheck = $input['sn_alertFriendReq'];
        }

        if ($sn_alertPost == 1) $sn_postcheck = "checked";
        else $sn_postcheck = "";
        if ($sn_alertFriend == 1) $sn_likecheck = "checked";
        else $sn_likecheck = "";
        if ($sn_alertLike == 1) $sn_friendcheck = "checked";
        else $sn_friendcheck = "";
        if ($sn_alertMention == 1) $sn_mentioncheck = "checked";
        else $sn_mentioncheck = "";
        if ($sn_alertfriendReqcheck == 1) $sn_friendReqcheck = "checked";
        else $sn_friendReqcheck = "";

        if ($pm == 1) {
            eval("\$socialnetwork_ucp_pmAlert .= \"" . $templates->get('socialnetwork_ucp_pmAlert') . "\";");
        } else {
            $socialnetwork_ucp_pmAlert = "";
        }
        $fields = getOwnFields();
        if (empty($fields)) $socialnetwork_ucp_ownFieldsBit = "Keine weiteren Felder.";

        foreach ($fields as $field) {
            $sn_fieldtitle = $field;
            $get_input  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $mybb->user['uid']), "own_" . $field);
            eval("\$socialnetwork_ucp_ownFieldsBit .= \"" . $templates->get('socialnetwork_ucp_ownFieldsBit') . "\";");
        }

        eval("\$page = \"" . $templates->get('socialnetwork_ucp_main') . "\";");
        output_page($page);
        die();
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
        ('$thisuser', '$nickname','$avatar','$titelbild','$alertPost','$alertFriend','$alertLike','$alertMention'" . $strownIns . ") 
        ON DUPLICATE KEY UPDATE 
        sn_nickname='$nickname', sn_avatar='$avatar', sn_userheader='$titelbild', 
        sn_alertPost = '$alertPost', sn_alertLike='$alertLike', sn_alertFriend='$alertFriend', sn_alertMention ='$alertMention'
        " . $strUpdate . "");

        redirect('usercp.php?action=socialnetwork');
    }
}


/***
 * The Mainpage of Network, bundle all the work
 */
$plugins->add_hook("member_profile_start", "socialnetwork_mainpage");
function socialnetwork_mainpage()
{
    global $db, $mybb, $lang, $templates, $infinitescrolling, $cache, $page, $headerinclude, $header, $footer, $usercpnav, $theme, $socialnetwork_member_postbit, $socialnetwork_member_infobit, $socialnetwork_member_friendsbit, $socialnetwork_member_postimg, $socialnetwork_member_friends, $socialnetwork_member_friendsAddDelete, $sn_page_profil;
    $lang->load('socialnetwork');

    $usergroups_cache = $cache->read("usergroups");
    $thisuser = intval($mybb->user['uid']);
    $userUseSN = 1;
    $userspageid = intval($mybb->input['uid']);

    //$logo = '<img src="' . $mybb->settings['socialnetwork_logo'] . '" alt="social logo"/>';
    $logo = "<img src=\"" . $mybb->settings['socialnetwork_logo'] . "\"/>";
    $url = $mybb->settings['bburl'];
    $userUseSNQuery = $db->fetch_field($db->simple_select("sn_users", "uid", "uid = $thisuser"), "uid");
    if ($userUseSNQuery == "") {
        $userUseSN = 0;
    }
    if ($mybb->input['area'] == "socialnetwork") {
        //not allowed to use social network
        if (!$mybb->usergroup['socialnetwork_isallowed']) {
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
        $sn_thispage = getSnUserInfo(intval($mybb->input['uid']));

        //user have no page
        if ($sn_thispage == 0) error_no_permission();
        //  $sn_page_profil = "<a href=\"" . $url . "member.php?action=profile&uid=" . $sn_thispage['uid'] . "&area=socialnetwork\">";
        $socialnetwork_view = $lang->socialnetwork_view;
        $lang->socialnetwork_view = $lang->sprintf($socialnetwork_view, $sn_thispage['sn_nickname']);
        $sn_page = "<a href=\"" . $url . "member.php?action=profile&uid=" . $sn_thispage['uid'] . "&area=socialnetwork\">" . $lang->socialnetwork_view . "</a>";

        //Now we want the individual fields
        $fields = getOwnFields();
        // $socialnetwork_ucp_ownFieldsBit
        $getOrder = $db->escape_string($mybb->settings['socialnetwork_orderOffFields']);
        if ($getOrder == "") {
            $fields = getOwnFields();
            if (empty($fields)) $socialnetwork_member_infobit = "";
            foreach ($fields as $field) {
                $own_title = $field;
                $own_value  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $sn_thispage['uid']), "own_" . $field);

                eval("\$socialnetwork_member_infobit .= \"" . $templates->get('socialnetwork_member_infobit') . "\";");
            }
        } else {
            $orderArray = explode(',',  $getOrder);
            foreach ($orderArray as $order) {
                foreach ($fields as $field) {
                    if ($order == $field) {
                        $sn_fieldtitle = $field;

                        $get_value  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $sn_thispage['uid']), "own_" . $field);
                        $own_title = $field;
                        if ($get_value == "") $own_value = $lang->socialnetwork_member_ownNotFilled;
                        else $own_value = $get_value;
                        eval("\$socialnetwork_member_infobit .= \"" . $templates->get('socialnetwork_member_infobit') . "\";");
                    }
                }
            }
        }

        $thispage = intval($sn_thispage['uid']);

        if (isset($mybb->input['sendPost']) && $userUseSN == 1) {
            $postid = getNextId("sn_posts");
            if (isset($_FILES['uploadImg']['name']) && $_FILES['uploadImg']['name'] != '') {
                uploadImg($postid, "post");
            }
            $datetime = $db->escape_string($mybb->input['datum'] . " " . $mybb->input['sn_uhrzeit']);
            $post = $db->escape_string($mybb->input['sn_post']);

            checkMentions("post", $thispage, $thisuser, $postid, 0);
            if ($post != '') {
                mentionUser($post, $thispage, $postid, 0);
                savingPostOrAnswer($thispage, $thisuser, $datetime, $post, "sn_posts");
            } else {
                echo "<script>alert('" . $lang->socialnetwork_member_errorMessageEmpty . ".');</script>";
            }
        } else if (isset($mybb->input['sendPost']) && $userUseSN == 0) {
            echo "<script>alert('" . $lang->socialnetwork_member_errorNoOwnPage . ".');</script>";
        }

        if (isset($mybb->input['sendAnswer']) && $userUseSN == 1) {
            $toPostId = intval($mybb->input['postid']);
            $answerid = getNextId("sn_answers");
            $datetime = $db->escape_string($mybb->input['sn_ansDatum'] . " " . $mybb->input['sn_ansUhrzeit']);
            $answer = $db->escape_string($mybb->input['sn_answer']);

            if (isset($_FILES['uploadImg']['name']) && $_FILES['uploadImg']['name'] != '') {
                uploadImg($answerid, "answer");
            }
            checkMentions("answer", $thispage, $thisuser, $toPostId, $answerid);
            if ($answer != '') {
                mentionUser($answer, $thispage, $toPostId, $answerid);
                savingPostOrAnswer($toPostId, $thisuser, $datetime, $answer, "sn_answers");
            } else {
                echo "<script>alert('" . $lang->socialnetwork_member_errorMessageEmpty . ".');</script>";
            }
        } else if (isset($mybb->input['sendAnswer']) && $userUseSN == 0) {
            echo "<script>alert('" . $lang->socialnetwork_member_errorNoOwnPage . ".');</script>";
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
        if (isset($mybb->input['saveImgpost'])) {
            uploadImg(intval($mybb->input['postid']), "post");
        }
        if (isset($mybb->input['saveImgans'])) {
            uploadImg(intval($mybb->input['ansid']), "answer");
        }
        if ($mybb->input['deleteImgPid'] != "" && is_numeric($mybb->input['deleteImgPid'])) {
            $todelete = intval($mybb->input['deleteImgPid']);
            $typeis = $db->escape_string($mybb->input['type']);
            deleteImgs($todelete, $typeis);
        }

        $sn_postid = intval($mybb->input['postid']);
        $sn_ansid = intval($mybb->input['ansid']);
        $sn_uid = intval($mybb->user['uid']);

        if ($mybb->input['like'] == 'like') {
            checkMentions("like", $thispage, $thisuser, $sn_postid, $sn_ansid);
            like($thispage, $sn_postid, $sn_ansid, $sn_uid, "page");
        }
        if ($mybb->input['like'] == 'dislike') {
            dislike($thispage, $sn_postid, $sn_ansid, $sn_uid, "page");
        }

        if ($mybb->input['postdelete'] != "" && is_numeric($mybb->input['postdelete'])) {
            $toDelete = intval($mybb->input['postdelete']);
            deletePost($toDelete, $thispage);
        }
        if ($mybb->input['ansdelete'] != "" && is_numeric($mybb->input['ansdelete'])) {
            $toDelete = intval($mybb->input['ansdelete']);
            deleteAnswer($toDelete, $thispage);
        }
        showFriends();
        //infinite scrolling or without?  
        if ($socialnetwork_scrolling == 1) {
            showPostsAjax();
        } else {
            showPostsNormal();
        }
        eval("\$page = \"" . $templates->get('socialnetwork_member_main') . "\";");
        output_page($page);
        die();
    }

    $sn_page_profil = "<a href=\"" . $url . "/member.php?action=profile&uid=" .  $userspageid  . "&area=socialnetwork\">" . $lang->socialnetwork_view2 . "</a>";
}
/**
 * deletes a Post, and answers and images belonging to it
 * @param $toDelete Post which should be deleted
 * @param $thispage from wich page
 */
function deletePost($toDelete, $thispage)
{
    global $db, $mybb, $lang;
    $thisuser = intval($mybb->user['uid']);
    $postuid = $db->fetch_field($db->simple_select("sn_posts", "sn_uid", "sn_post_id = $toDelete"), "sn_uid");
    //we need all answers, cause we want to delete them to
    if (($thisuser == $postuid) || ($mybb->usergroup['canmodcp'] == 1)) {
        $getanswers = $db->simple_select("sn_answers", "*", "sn_post_id = $toDelete");
        while ($get_ans = $db->fetch_array($getanswers)) {
            $aid = $get_ans['sn_aid'];
            deleteAnswer($aid, 0);
            deleteLikes($aid, "answer");
        }
        deleteImgs($toDelete, "post");
        deleteLikes($toDelete, "post");
        $db->delete_query("sn_posts", "sn_post_id = $toDelete");
        $db->delete_query("sn_answers", "sn_post_id = $toDelete");
        redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
    } else {
        echo "<script>alert('" . $lang->socialnetwork_member_errorMessageDelete . "')</script>";
    }
}

/**
 * deletes an answer and images belonging to it
 * @param $toDelete Post which should be deleted
 * @param $thispage from wich page - if 0 -> function call from deletePost, we don't want to redirect
 */
function deleteAnswer($toDelete, $thispage)
{
    global $db, $mybb, $lang;
    $thisuser = intval($mybb->user['uid']);
    $postuid = $db->fetch_field($db->simple_select("sn_answers", "sn_uid", "sn_aid = $toDelete"), "sn_uid");
    deleteImgs($toDelete, "answer");
    deleteLikes($toDelete, "answer");
    if (($thisuser == $postuid) || ($mybb->usergroup['canmodcp'] == 1)) {
        $db->delete_query("sn_answers", "sn_aid = $toDelete");
        if ($thispage != 0) {
            redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
        }
    } else {
        echo "<script>alert('" . $lang->socialnetwork_member_errorMessageDelete . "')</script>";
    }
}


/**
 * handle when user is deleted -> nickname empty? save username, else keep nickname
 * Was passiert wenn ein User gelöscht wird?
 * Was passiert mit den geposteten Beiträgen?
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "socialnetwork_userdelete");
function socialnetwork_userdelete()
{
    global $db, $cache, $mybb, $user;
    $todelete = (int)$user['uid'];
    $snData = getSnUserInfo($todelete);
    $name = $snData['sn_nickname'];

    $updateArr = array(
        'sn_del_name' => $name
    );
    $db->update_query("sn_posts", $updateArr, "sn_uid ='" . $todelete . "'");
    $db->update_query("sn_answers", $updateArr, "sn_uid ='" . $todelete . "'");
    $db->delete_query("sn_friends", "sn_uid = $todelete OR sn_friendith = $todelete");
    $db->delete_query("sn_likes", "sn_uid= $todelete");
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

/** **
 * Upload of images
 * @param int $id to which id of Post or answer
 * @param string $type post or answer
 ** ***/
function uploadImg($id, $type)
{
    global $db, $mybb, $lang;

    $uploadImgWidth = intval($mybb->settings['socialnetwork_uploadImgWidth']);
    $uploadImgHeight = intval($mybb->settings['socialnetwork_uploadImgHeight']);
    $maxfilesize = intval($mybb->settings['socialnetwork_uploadImgSize']);
    $fail = false;
    $sizes = getimagesize($_FILES['uploadImg']['tmp_name']);

    $imgpath = "social/userimages/";
    // Check if gallery path is writable
    if (!is_writable('social/userimages/')) {
        echo "<script>alert('" . $lang->socialnetwork_upload_errorPath . "')</script>";
    }

    if ($sizes === false) {
        @unlink($imgpath);
        move_uploaded_file($_FILES['uploadImg']['tmp_name'], 'upload/' . $_FILES['uploadImg']['name']);
        $_FILES['uploadImg']['tmp_name'] = $imgpath;
        $sizes = getimagesize($_FILES['uploadImg']['tmp_name']);
        $fail = true;
    }

    // No size, so something could be wrong with image
    if ($sizes === false) {
        echo "<script>alert('" . $lang->socialnetwork_upload_errorSizes . "')</script>";
    } elseif ((!empty($uploadImgWidth) && $sizes[0] >  $uploadImgWidth) || (!empty($uploadImgHeight) && $sizes[1] > $uploadImgHeight)) {
        @unlink($_FILES['uploadImg']['tmp_name']);  //delete 
        echo "<script>alert('" . $lang->socialnetwork_upload_errorSizes . "')</script>";
    } else {

        $filesize = $_FILES['uploadImg']['size'];
        if (!empty($maxfilesize) && $filesize > $maxfilesize) {
            @unlink($_FILES['uploadImg']['tmp_name']); //delete
            echo "<script>alert('" . $lang->socialnetwork_upload_errorFileSize . "')</script>";
        }

        $filetypes = array(
            1 => 'gif',
            2 => 'jpeg',
            3 => 'png',
            4 => 'bmp',
            5 => 'tiff',
            6 => 'jpg',
        );

        if (isset($filetypes[$sizes[2]])) {
            $filetyp = $filetypes[$sizes[2]];
        } else {
            $filetyp = '.bmp';
        }
        $filename = $mybb->user['uid'] . '-' . date('d_m_y_g_i_s') . '.' . $filetyp;

        if ($fail == false) {
            move_uploaded_file($_FILES['uploadImg']['tmp_name'], $imgpath . $filename);
        } else {
            rename($_FILES['uploadImg']['tmp_name'], $imgpath . $filename);
        }
        @chmod($imgpath . $filename, 0644);
        $db->write_query("INSERT INTO " . TABLE_PREFIX . "sn_imgs
						(sn_filesize, sn_filename, sn_width, sn_height, sn_uid, sn_postId, sn_type)
						VALUES ( $filesize,'$filename', $sizes[0], $sizes[1], " . $mybb->user['uid'] . ", $id, '$type')");
    }
}
/**
 * Handle everything to show Posts, loading all Posts 
 */
function showPostsNormal()
{
    global  $thispage, $db, $lang, $mybb, $templates, $parser, $socialnetwork_member_postbit, $socialnetwork_member_answerbit, $socialnetwork_member_postimg;
    //Parser options

    $thispage = intval($mybb->input['uid']);

    $queryPosts = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "sn_posts WHERE sn_pageid = $thispage ORDER BY sn_date DESC, sn_post_id ASC");

    showPosts($queryPosts, "normal");
}


/** *****
 * INFINITE SCROLLING
 * This function is working like the show Post, but initital just showing the first 10 posts, 
 * the next 5 posts are loaded if you reach the end of the page
 * This function could handle infinite scrolling(like facebook), you can use this instead of 'showPostsNormal()' 
 * settings in acp
 * but beware, direct links from notifications may not be working, when post/answer isn't already loaded
 ***** */
function showPostsAjax()
{
    global  $thispage, $db, $lang, $mybb, $templates, $parser, $infinitescrolling, $socialnetwork_member_postbit, $socialnetwork_member_answerbit, $socialnetwork_member_postimg;

    $offset = 0;
    $no_of_records_per_page = $mybb->settings['socialnetwork_recordsperpage'];
    if ($no_of_records_per_page == "") $no_of_records_per_page = 5;
    $thispage = intval($mybb->input['uid']);

    $queryPosts = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "sn_posts WHERE sn_pageid = $thispage ORDER BY sn_date DESC, sn_post_id ASC LIMIT $offset, $no_of_records_per_page");

    // $queryPosts = $db->simple_select("sn_posts", "*", "sn_pageid = $thispage", array(
    //     "order_by" => 'sn_date, sn_post_id',
    //     "order_dir" => 'DESC',
    //     "limit start" => $offset,
    //     "limit" => $no_of_records_per_page
    // ));
    showPosts($queryPosts, "infinite");
}

/***
 * showPosts()
 */
function showPosts($query, $type)
{
    global  $thispage, $db, $lang, $mybb, $templates, $parser, $socialnetwork_member_postbit, $socialnetwork_member_answerbit, $socialnetwork_member_postimg, $infinitescrolling, $socialnetwork_misc_postbit, $socialnetwork_misc_answerbit;
    $options = array(
        "allow_html" => $mybb->settings['socialnetwork_html'],
        "allow_mycode" => $mybb->settings['socialnetwork_mybbcode'],
        "allow_imgcode" => $mybb->settings['socialnetwork_img'],
        "filter_badwords" => $mybb->settings['socialnetwork_badwords'],
        "nl2br" => 1,
        "allow_videocode" => $mybb->settings['socialnetwork_videos'],
    );


    $thispage = intval($mybb->input['uid']);

    $thispagedata = getSnUserInfo($thispage);
    $thisuser = intval($mybb->user['uid']);
    $thisusersndata = getSnUserInfo($thisuser);
    $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
    $cnt_likes_post = "";

    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;

    while ($get_post = $db->fetch_array($query)) {
        //stuff for newsfeed

        $likevar = "like";
        $sn_like = $lang->socialnetwork_member_like;
        if ($type == "infinite") {
            $infinitescrolling = '<span style="text-align:center; display:block;"><img id="loader" src="images/spinner.gif"></div>';
        } else {
            $infinitescrolling = "";
        }
        //show the image beside the anwser form
        $sn_ansFormImg = $thisusersndata['sn_avatar'];
        if ($sn_ansFormImg == "") $sn_ansFormImg = $defaultava;
        //poster uid
        $postuser = intval($get_post['sn_uid']);
        $postuserdata = getSnUserInfo($postuser);
        $name = $postuserdata['sn_nickname'];

        //we want to link to the social page of the poster
        $sn_postname = '<a href="member.php?action=profile&uid=' . $postuser . '&area=socialnetwork">' . $name . '</a>';
        //the avatar
        $sn_postimg =  $postuserdata['sn_avatar'];


        if ($get_post['sn_del_name'] != "") {
            $sn_postname =  htmlspecialchars_uni($get_post['sn_del_name']);
            $sn_postimg = $defaultava;
        }

        //the other informations of post
        $sn_date = date('d.m.y - H:i', strtotime($get_post['sn_date']));

        $sn_showPost = $parser->parse_message($get_post['sn_social_post'], $options);
        $sn_postid = intval($get_post['sn_post_id']);
        if ($type == "newsfeed") {
            $pageid = getPageId($sn_postid, "post");
            $posturl = $mybb->settings['bburl'] . "/member.php?action=profile&uid=" . $pageid . "&area=socialnetwork#" . $sn_postid;
        }
        $sn_post_ed_del = "";
        //edit and delete
        if (($thisuser == $postuser) || ($mybb->usergroup['canmodcp'] == 1)) {
            $sn_date_date = date('Y-m-d', strtotime($get_post['sn_date']));
            $sn_date_time = date('H:i', strtotime($get_post['sn_date']));
            eval("\$sn_post_ed_del = \"" . $templates->get("socialnetwork_member_postedit") . "\";");
        }

        //we have to clear the variables first
        $socialnetwork_member_answerbit = "";
        //  $socialnetwork_misc_answerbit = "";
        $socialnetwork_misc_postimg = "";
        $socialnetwork_member_postimg = "";
        //Get all likes
        $likeQuery = $db->simple_select("sn_likes", "*", "sn_postid = $sn_postid");
        while ($likesarray = $db->fetch_array($likeQuery)) {
            //do the user already like the post? -> then we want to show the dislike stuff
            if ($likesarray['sn_uid'] == $thisuser) {
                $likevar = "dislike";
                $sn_like = $lang->socialnetwork_member_dislike;
            }
        }
        //count likes
        $cnt_likes_post = $db->fetch_field($db->simple_select("sn_likes", "count(*) as cnt", "sn_postid = $sn_postid"), "cnt");

        //Do the user upload an image to the post?
        $postImg = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = $sn_postid and sn_type = 'post'"));

        if ($thisuser == $postuser || $mybb->usergroup['canmodcp'] == 1) {
            $socialnetwork_member_postimg = "<span id=\"post" . $sn_postid . "\"><button onClick=\"addImg('post','" . $sn_postid . "')\"  class=\"editDelete\"><i class=\"fas fa-camera-retro\"></i></button></span>";
            $socialnetwork_misc_postimg = "";
        } else {
            $socialnetwork_member_postimg = "";
            $socialnetwork_misc_postimg = "";
        }
        if (!empty($postImg)) {
            $postImgFilename = $postImg['sn_filename'];
            $postImgId = $postImg['sn_imgId'];
            if ($thisuser == $postImg['sn_uid'] || $mybb->usergroup['canmodcp'] == 1) {
                $manage_img = "<a href=\"member.php?action=profile&uid=" . $thispage . "&area=socialnetwork&deleteImgPid=" . $sn_postid . "&type=post\" class=\"editDelete\" ><i class=\"fas fa-trash\"></i></a>";
            }
            if ($type == "newsfeed") {
                eval("\$socialnetwork_misc_postimg = \"" . $templates->get('socialnetwork_misc_postimg') . "\";");
            }
            eval("\$socialnetwork_member_postimg = \"" . $templates->get('socialnetwork_member_postimg') . "\";");
        }
        //variale to count the likes of an answer
        $cnt_likes_ans = "";
        //and here we get the answers for the actual post
        $queryAnswer = $db->simple_select("sn_answers", "*", "sn_post_id = $sn_postid", array(
            "order_by" => 'sn_date, sn_aid',
            "order_dir" => 'DESC'
        ));
        $sn_ans_ed_del = "";
        $socialnetwork_member_answerbit = "";
        $socialnetwork_misc_answerbit = "";
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
                if ($likesarray['sn_uid'] == $thisuser) {
                    $likevar_ans = "dislike";
                    $sn_like_ans = $lang->socialnetwork_member_dislike;
                }
            }
            //uid of answer
            $sn_ansUser = intval($get_answer['sn_uid']);
            $sn_ansUserData = getSnUserInfo($sn_ansUser);
            //avatar 
            $sn_anspostimg = $sn_ansUserData['sn_avatar'];
            //name (nickname or username?)
            $ansname =   $sn_ansUserData['sn_nickname'];
            $sn_ansname = '<a href="member.php?action=profile&uid=' . $sn_ansUser . '&area=socialnetwork">' . $ansname . '</a>';

            //handle of deleted user
            if ($get_answer['sn_del_name'] != "") {
                $sn_ansname =  htmlspecialchars_uni($get_answer['sn_del_name']);
                $sn_anspostimg = $defaultava;
            }
            //edit delete Image/ show image etc
            if ($thisuser == $sn_ansUser || $mybb->usergroup['canmodcp'] == 1) {
                //no image, show add button
                $socialnetwork_member_postimg_ans = "<span id=\"ans" . $ansid . "\"><button onClick=\"addImg('ans','" . $ansid . "')\" id=\"sn_addimg\" class=\"editDelete\"><i class=\"fas fa-camera-retro\"></i></button></span>";
                $socialnetwork_misc_postimg_ans = "";
            } else {
                $socialnetwork_member_postimg_ans = "";
                $socialnetwork_misc_postimg_ans = "";
            }
            $postImgAns = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = $ansid and sn_type = 'answer'"));
            if (!empty($postImgAns)) {
                $postImgFilename = $postImgAns['sn_filename'];
                $postImgId = $postImgAns['sn_imgId'];
                if ($thisuser == $postImg['sn_uid'] || $mybb->usergroup['canmodcp'] == 1) {
                    $manage_img = "<a href=\"member.php?action=profile&uid=" . $thispage . "&area=socialnetwork&deleteImgPid=" . $ansid . "&type=answer\" class=\"editDelete\" ><i class=\"fas fa-trash\"></i></a>";
                }
                if ($type == "newsfeed") {
                    eval("\$socialnetwork_misc_postimg_ans = \"" . $templates->get('socialnetwork_misc_postimg') . "\";");
                }
                eval("\$socialnetwork_member_postimg_ans = \"" . $templates->get('socialnetwork_member_postimg') . "\";");
            }
            $sn_ansdate = date('d.m.y - H:i', strtotime($get_answer['sn_date']));
            $sn_ans_ed_del = "";
            //edit and delete
            if (($thisuser == $sn_ansUser) || ($mybb->usergroup['canmodcp'] == 1)) {
                // eval("\$sn_post_ed_del = \"".$templates->get("socialnetwork_member_postedit")."\";");
                $ansdate = date('Y-m-d', strtotime($get_answer['sn_date']));
                $anstime = date('H:i', strtotime($get_answer['sn_date']));
                eval("\$sn_ans_ed_del = \"" . $templates->get("socialnetwork_member_answeredit") . "\";");
            }

            $sn_showAnswer = $parser->parse_message($get_answer['sn_answer'], $options);
            if ($type == "newsfeed") {

                eval("\$socialnetwork_misc_answerbit .= \"" . $templates->get('socialnetwork_misc_answerbit') . "\";");
            } else {

                eval("\$socialnetwork_member_answerbit .= \"" . $templates->get('socialnetwork_member_answerbit') . "\";");
            }
        }
        if ($type == "newsfeed") {

            eval("\$socialnetwork_misc_postbit .= \"" . $templates->get('socialnetwork_misc_postbit') . "\";");
        } else {
            eval("\$socialnetwork_member_postbit .= \"" . $templates->get('socialnetwork_member_postbit') . "\";");
        }
    }
}
/**
 * Handle everything to show and add friends
 */
function showFriends()
{
    global $db, $mybb, $templates, $lang, $socialnetwork_member_friends, $socialnetwork_member_friendsbit, $socialnetwork_member_friendsbitAsked, $socialnetwork_member_friendsAddDelete;

    $thisuser = intval($mybb->user['uid']);
    $usesSN = getSnUserInfo($thisuser);
    $allowed = 1;
    if ($usesSN['uid'] == "") {
        $allowed = 0;
    }

    $thispage = intval($mybb->input['uid']);
    $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
    $flagFriends = 0;
    //Query to check if already friends
    $friendquery = $db->simple_select("sn_friends", "*", "(sn_uid = '$thisuser' AND sn_friendwith = '$thispage') OR (sn_uid = '$thispage' AND sn_friendwith = '$thisuser') ");
    $friendValue = "plus";
    if ($db->num_rows($friendquery) > 0) {
        $friendValue = "minus";
        $flagFriends = "," . $thisuser . "," . $thispage . ",";
    }

    //Check if this user has allready asked for friendship
    $friendqueryAsked = $db->simple_select("sn_friends", "*", "(sn_uid = '$thispage' AND sn_friendwith = '$thisuser') AND sn_accepted=0");

    //Get all Friends of this active Page
    $queryFriends = $db->simple_select("sn_friends", "*", "sn_uid = $thispage OR (sn_friendwith = $thispage AND sn_accepted = 0)");

    // $socialnetwork_member_friendsbitToAccept = "";
    $titcnt = 0;
    $titAccCnt = 0;
    while ($get_friend = $db->fetch_array($queryFriends)) {
        $friend = $get_friend['sn_friendwith'];

        //Get Data of friend
        $frienddataSN = getSnUserInfo($friend);
        $friendava = $frienddataSN['sn_avatar'];
        $friendname = "<a href=\"" . get_profile_link($friend) . "&area=socialnetwork\">" . $frienddataSN['sn_nickname'] . "</a>";
        if ($thisuser == $thispage) {
            $frienddelete = "<a href=\"member.php?action=profile&uid=" . $thispage . "&area=socialnetwork&friend=minus&friendid=" . $friend . "\">" . $lang->socialnetwork_member_delete . "</a>";
        }

        //no friends at the moment
        if ($get_friend['sn_accepted'] != 0) {
            eval("\$socialnetwork_member_friendsbit .= \"" . $templates->get('socialnetwork_member_friendsbit') . "\";");
        } else if ($thisuser == $thispage && $get_friend['sn_uid'] == $thispage) {
            //friends to accept
            $titAccCnt++; //counting for get the title once
            if ($titAccCnt == 1) $socialnetwork_member_friendsbitToAccept = $lang->socialnetwork_member_openRequestFriendTit;
            eval("\$socialnetwork_member_friendsbitToAccept .= \"" . $templates->get('socialnetwork_member_friendsbitToAccept') . "\";");
        } else if ($thisuser == $thispage && $get_friend['sn_friendwith'] == $thispage) {
            //friends asked
            $titcnt++;
            if ($titcnt == 1) { //get title once
                $socialnetwork_member_friendsbitAsked = $lang->socialnetwork_member_openRequestFriendAskedTit;
            };

            $askedFriendSN = getSnUserInfo($get_friend['sn_uid']);
            $friendava = $askedFriendSN['sn_avatar'];
            $friendname = "<a href=\"" . get_profile_link($get_friend['sn_uid']) . "&area=socialnetwork\">" . $askedFriendSN['sn_nickname'] . "</a>";

            eval("\$socialnetwork_member_friendsbitAsked .= \"" . $templates->get('socialnetwork_member_friendsbitAsked') . "\";");
        }
    }

    if ($thisuser != $thispage) {
        $socialnetwork_member_friendsAddDelete = "
        <a href=\"member.php?action=profile&uid=" . $thispage . "&area=socialnetwork&friend=" . $friendValue . "&friendid=" . $thispage . "\"><span class=\"fas fa-user-" . $friendValue . "\" aria-label=\"" . $friendValue . "\" id=\"friendAddRemove\"></span></a>";
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
            checkMentions("friend", $thispage, $friendid, 0, 0);
            addFriend($friendid, $thisuser);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    if ($mybb->input['friend'] == "minus" && ($thispage == intval($mybb->input['friendid']))) {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            deleteFriend($friendid, $thisuser, $flagFriends);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    if ($mybb->input['friend'] == "accept" && ($thisuser == $thispage)) {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            checkMentions("friendRequest", $thispage, $friendid, 1, 0);
            acceptFriend($friendid, $thisuser, $thispage);
        } else {
            echo '<script>alert("' . $lang->socialnetwork_member_toFriendNotAllowed . '")</script>';
        }
    }
    if ($mybb->input['friend'] == "deny" && ($thisuser == $thispage)) {
        if ($allowed == 1) {
            $friendid = intval($mybb->input['friendid']);
            checkMentions("friendRequest", $thispage, $friendid, 0, 0);
            denyFriend($friendid, $thisuser, $thispage);
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
 * @param string $flagFriends for checking if they're friends and friend (saving id of current user and thispage - if they are friends else it's 0) can delete friendship
 */
function deleteFriend($userid, $thisuser, $flagFriends)
{
    global $db, $mybb;

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
function acceptFriend($userid, $thisuser, $thispage)
{
    global $db;
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
function denyFriend($userid, $thisuser, $thispage)
{
    global $db, $mybb;
    $db->delete_query("sn_friends", "sn_uid = $userid AND sn_friendwith = $thisuser");
    $db->delete_query("sn_friends", "sn_uid = $thisuser AND sn_friendwith = $userid");
    $goto = intval($mybb->input['uid']);
    redirect('member.php?action=profile&uid=' .  $goto . '&area=socialnetwork');
}
/**
 * Function to find mentioned users
 * mention with @username oder 
 */
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
 * @param int $pid = postid if post //if FriendRequest -> 1 for accept, 0 for deny
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

            //is My Alert really installed? 
            if ($alert == 1 && class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
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

            //The owner of page = page id
            //function checkMentions($type, $pageid, $uid, $pid, $aid)
            // checkMentions("like", $thispage, $thisuser, $sn_postid, $sn_ansid);
            if ($alert == 1 && class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('sn_Answer');
                //Not null, the user wants an alert and the user is not on his own page.
                if ($alertType != NULL && $alertType->getEnabled() && $pageid != $uid) {
                    //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$pageid, $alertType, (int)$id);
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
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$posteruid, $alertType, (int)$id);
                    $alert->setExtraDetails([
                        'answerid' => $aid,
                        'pageid' => $pageid,
                        'fromuser' => $uid,
                        'postid' => $pid //$answertopostid?
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            break;
        case "like":
            // checkMentions("like", $thispage, $thisuser, $sn_postid, $sn_ansid);
            //function checkMentions($type, $pageid, $uid, $pid, $aid)
            if ($pid != 0 && $aid == 0) {
                //POST
                //we need to get the author of post, to know if he want an alert
                $authorpost = $db->fetch_field($db->simple_select("sn_posts", "sn_uid", "sn_post_id = " . $pid), "sn_uid");
                $authorAlert = $db->fetch_field($db->simple_select("sn_users", "sn_alertLike", "uid =" . $authorpost), "sn_alertLike");
                $pid = $pid;
            } else if ($aid != 0) {
                $authorpost = $db->fetch_field($db->simple_select("sn_answers", "sn_uid", "sn_aid = " . $aid), "sn_uid");
                $authorAlert = $db->fetch_field($db->simple_select("sn_users", "sn_alertLike", "uid =" . $authorpost), "sn_alertLike");
                $pid = "ans" . $aid;
            }
            if ($pm == 1 && $authorAlert == 1) {
                $socialnetwork_pm_like = $lang->socialnetwork_pm_like;
                $lang->socialnetwork_pm_like = $lang->sprintf($socialnetwork_pm_like, $thisusername, $pageid, $pid);
                $pm = array(
                    "subject" => $lang->socialnetwork_pm_likeSubject,
                    "message" => $lang->socialnetwork_pm_like,
                    "fromid" => $uid,
                    "toid" => $authorpost
                );
                $pmhandler->set_data($pm);
                if (!$pmhandler->validate_pm()) {
                    $pm_errors = $pmhandler->get_friendly_errors();
                    return $pm_errors;
                } else {
                    $pminfo = $pmhandler->insert_pm();
                }
            }

            if ($alert == 1 && class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('sn_Like');

                //Not null, the user wants an alert and the user is not on his own page.
                if ($alertType != NULL && $alertType->getEnabled()) {
                    //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$authorpost, $alertType, (int)$id);
                    //some extra details
                    $alert->setExtraDetails([
                        'pid' => $pid,
                        'pageid' => $pageid,
                        'fromuser' => $thisusername
                    ]);
                    //add the alert
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }
            break;
        case "friend":
            //this user stellt anfrage
            //page seite muss informiert werden 
            $friendAlert  = $db->fetch_field($db->simple_select("sn_users", "*", "uid = " . $uid), "sn_alertPost");
            if ($pm == 1 && $friendAlert == 1) {
                $socialnetwork_pm_friend = $lang->socialnetwork_pm_friend;
                $lang->socialnetwork_pm_friend = $lang->sprintf($socialnetwork_pm_friend, $thisusername, $pageid);
                $pm = array(
                    "subject" => $lang->socialnetwork_pm_friendSubject,
                    "message" => $lang->socialnetwork_pm_friend,
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
            if ($alert == 1 && class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('sn_Friend');

                //Not null, the user wants an alert and the user is not on his own page.
                if ($alertType != NULL && $alertType->getEnabled()) {
                    //constructor for MyAlert gets first argument, $user (not sure), second: type  and third the objectId 
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$uid, $alertType, (int)$id);
                    //some extra details
                    $alert->setExtraDetails([
                        'friendid' => $uid,
                        'pageid' => $pageid,
                        'fromuser' => $thisusername
                    ]);
                    //add the alert
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }
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
        case "friendRequest":
            $friendAlert  = $db->fetch_field($db->simple_select("sn_users", "*", "uid = " . $uid), "sn_alertPost");
            if ($pm == 1 && $friendAlert == 1) {
                if ($pid == 1) {
                    $socialnetwork_pm_friendReqAcpt = $lang->socialnetwork_pm_friendReqAcpt;
                    $lang->socialnetwork_pm_friendReqAcpt = $lang->sprintf($socialnetwork_pm_friendReqAcpt, $thisusername);
                    $message = $lang->socialnetwork_pm_friendReqAcpt;
                }
                if ($pid == 0) {
                    $socialnetwork_pm_friendReqDeny = $lang->socialnetwork_pm_friendReqDeny;
                    $lang->socialnetwork_pm_friendReqDeny = $lang->sprintf($socialnetwork_pm_friendReqDeny, $thisusername);
                    $message = $lang->socialnetwork_pm_friendReqDeny;
                }
                $pm = array(
                    "subject" => $lang->socialnetwork_pm_friendSubject,
                    "message" => $message,
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
            break;
    }
}

/**
 * Save Post or Answer
 * @param int $id thispage (for post) or postid(for answer)
 * @param int $thisuser (who has sent the post)
 * @param string $date (choosen date and time)
 * @param string $message (content)
 * @param string $type (post or answer)
 */
function savingPostOrAnswer($id, $thisuser, $date, $message, $type)
{
    global $db;
    //ich unterscheide hier erst ob ich eine antwort oder einen post speichern möchte.
    // wichtig für dich ist also eigentlich nur, (du wirst die unterscheidung so nicht haben), du brauchst ein array
    //link stehen die feldnamen, wie in der datenbank, rechts die werte die eingetragen werden. Die übergebe ich hier, über die funktion.
    // du wirst sie dir wahrscheinlich direkt über ein input feld holen, sagen wir du hast eine Textarea die "geheimnis" heißt.
    // dann würdest du $mybb->input['gehemnis']  auf die rechte seite schreiben :) also z.B: "sn_social_post" => $mybb->input['gehemnis'], 
    switch ($type) {
        case "sn_posts":
            //Hier ist das array: 
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

    //wir benutzen hier jetzt einfach die mybb funktion, du sagst ihm einfach welche tabelle(ohne den präfix) und welches Array
    //(ich übergebe wieder den Tabellen namen  durch eine Variable)
    //in deinem fall wäre das zum beispiel:  
    //$db->insert_query("geheimnistabelle", $insert_array);
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
 * @param int $thispage
 * @param int $sn_postid
 * @param int $sn_ansid
 * @param int $sn_uid
 * @param string $type newsfeed or page? 
 */
function like($thispage, $sn_postid, $sn_ansid, $sn_uid, $type)
{
    global $db;
    //workaround newsfeedpage
    if ($thispage == "newsfeed") {
        if ($sn_ansid == 0) $thispage == getPageId($sn_postid, "post");
        if ($sn_ansid != 0) $thispage == getPageId($sn_ansid, "answer");
    }
    $isokay = true;
    if ($sn_postid != 0) {
        //we need to check, if the user allready liked the post or the answer 
        $queryCheck = $db->simple_select("sn_likes", "*", "sn_postid = $sn_postid AND sn_uid = $sn_uid ");
        if (!empty($db->fetch_array($queryCheck))) {
            $isokay = false;
            if ($type == "page") {
                redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
            }
            if ($type == "newsfeed") {
                //      redirect('misc.php?action=sn_newsfeedAll');
            }
        }
    }
    if ($sn_ansid != 0) {
        $queryCheck = $db->simple_select("sn_likes", "*", "sn_answerid = $sn_ansid AND sn_uid = $sn_uid ");
        if (!empty($db->fetch_array($queryCheck))) {
            $isokay = false;
            //    redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
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
        if ($type == "page") {
            redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
        }
        if ($type == "newsfeed") {
            //        redirect('misc.php?action=sn_newsfeedAll');
        }
    }
}
/**
 * Disike a post/or Answer
 * @param int $thispage
 * @param int $sn_postid
 * @param int $sn_ansid
 * @param int $sn_uid
 * @param string $type newsfeed or page? 
 */
function dislike($thispage, $sn_postid, $sn_ansid, $sn_uid, $type)
{
    global $db;
    //we're disliking a post
    if ($sn_postid != 0) {
        $db->delete_query("sn_likes", "sn_uid = $sn_uid AND sn_postid = $sn_postid");
        if ($type == "page") {
            redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
        }
        if ($type == "newsfeed") {
            redirect('misc.php?action=sn_newsfeedAll');
        }
    }
    //we're disliking an answer
    if ($sn_ansid != 0) {
        $db->delete_query("sn_likes", "sn_uid = $sn_uid AND sn_answerid = $sn_ansid");
        if ($type == "page") {
            redirect('member.php?action=profile&uid=' . $thispage . '&area=socialnetwork');
        }
        if ($type == "newsfeed") {
            redirect('misc.php?action=sn_newsfeedAll');
        }
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
    $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
    $userArray = array();
    $userArray = ($db->fetch_array($db->simple_select("sn_users", "*", "uid = $userid", "limit 1")));
    if (!empty($userArray)) {

        if ($userArray['sn_nickname'] == "") {
            $userArray['sn_nickname'] = ($db->fetch_field($db->simple_select("users", "username", "uid = $userid", "limit 1"), "username"));
        }
        $userArray['sn_nickname'] = htmlspecialchars_uni($userArray['sn_nickname']);
        if ($userArray['sn_avatar'] == "") {
            $userArray['sn_avatar'] = $defaultava;
        } else {
            $userArray['sn_avatar'] = $userArray['sn_avatar'];
        }
        if ($userArray['sn_userheader'] == "") $userArray['sn_userheader'] = "";
        $userArray['sn_userheader'] = htmlspecialchars_uni($userArray['sn_userheader']);
    } else $userArray = 0;

    return $userArray;
}

/** 
 * get the pageid of post/answer
 */
function getPageId($postid, $type)
{
    global $db;
    if ($type == "post") {
        $pageid = $db->fetch_field($db->simple_select("sn_posts", "sn_pageid", "sn_post_id = " . $postid), "sn_pageid");
    }
    if ($type == "answer") {
        $post = $db->fetch_field($db->simple_select("sn_answers", "sn_post_id", "sn_aid = " . $postid), "sn_post_id");
        $pageid = $db->fetch_field($db->simple_select("sn_posts", "sn_pageid", "sn_post_id = " . $post), "sn_pageid");
    }
    return $pageid;
}

/**
 * Get the next id of an auto increment field in Database
 * @param string $tablename - name of table, from which we want the next id
 * @return int $lastId the id of next insert Post/Answer
 */
function getNextId($tablename)
{
    global $db;
    $databasename = $db->fetch_field($db->write_query("SELECT DATABASE()"), "DATABASE()");
    $lastId = $db->fetch_field($db->write_query("SELECT AUTO_INCREMENT FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = '" . $databasename . "' AND TABLE_NAME = '" . TABLE_PREFIX . $tablename . "'"), "AUTO_INCREMENT");
    return $lastId;
}

/** 
 * Delete an image from FileSystem
 * @param int $postid id of post, to which the image belongs
 * @param string $type post or answer
 */
function deleteImgs($postid, $type)
{
    global $db;
    $getImage = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = $postid AND sn_type='$type'"));
    $imgid = $getImage['sn_imgId'];
    if (!empty($getImage)) {
        $db->delete_query("sn_imgs", "sn_imgId = $imgid");
        unlink("social/userimages/" . $getImage['sn_filename']);
    }
}

/**
 * Delete Likes from database when post/anwser ist deleted
 * @param int $postid of post/answer
 * @param string $type post or answer
 */
function deleteLikes($postid, $type)
{
    global $db;
    if ($type == "post") {
        $db->delete_query("sn_likes", "sn_postid= $postid");
    }
    if ($type == "answer") {
        $db->delete_query("sn_likes", "sn_answerid= $postid");
    }
}

/**
 * Handle of the newsfeed pages 
 */
$plugins->add_hook("misc_start", "socialnetwork_newsfeed");
function socialnetwork_newsfeed()
{
    global $db, $mybb, $lang, $templates, $infinitescrolling, $cache, $page, $headerinclude, $header, $footer, $usercpnav, $theme,  $newsfeed_links, $socialnetwork_member_postbit, $socialnetwork_member_friendsbit, $socialnetwork_member_postimg, $socialnetwork_member_friends, $socialnetwork_member_friendsAddDelete, $socialnetwork_misc_postbit, $socialnetwork_misc_answerbit, $multipage;

    if ($mybb->input['action'] == "sn_newsfeedAll" || $mybb->input['action'] == "sn_newsfeedFriends") {
        add_breadcrumb($lang->socialnetwork_view_newsfeedAll, "misc.php?action=sn_newsfeedAll");

        $thisuser = intval($mybb->user['uid']);
        $userUseSN = 1;
        $numpages = $mybb->settings['threadsperpage'];
        if ($numpages == "") $numpages = 5;

        $userUseSNQuery = $db->fetch_field($db->simple_select("sn_users", "uid", "uid = $thisuser"), "uid");
        if ($userUseSNQuery == "") {
            $userUseSN = 0;
        }
        $usergroups_cache = $cache->read("usergroups");
        if (!$mybb->usergroup['socialnetwork_isallowed']) {
            error_no_permission();
        }
        //we want some pagination.
        $page = intval($mybb->input['page']);
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * 2;

        //show all posts of erveryone
        if ($mybb->input['action'] == "sn_newsfeedAll") {
            $queryPosts = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "sn_posts order by sn_date, sn_post_id DESC LIMIT $offset, $numpages");
            showPosts($queryPosts, "newsfeed");
            $numposts = $db->fetch_field($db->write_query("SELECT COUNT(sn_post_id) AS count FROM " . TABLE_PREFIX . "sn_posts"), "count");
            $newsfeed_links = "<b>Newsfeed aller Charaktere</b> - <a href=\"misc.php?action=sn_newsfeedFriends\">Newsfeed der Freunde</a>";
        }

        //show posts of friends 
        if ($mybb->input['action'] == "sn_newsfeedFriends") {
            $queryPostsFriends = $db->write_query(" 
            SELECT DISTINCT(sn_post_id), sn_pageid, sn_uid, sn_date, sn_social_post, sn_del_name FROM 
                (SELECT sn_post_id, sn_pageid, sn_uid, sn_date, sn_social_post, sn_del_name 
                    FROM (SELECT sn_friendwith FROM " . TABLE_PREFIX . "sn_friends WHERE sn_uid = $thisuser) as f 
                    JOIN " . TABLE_PREFIX . "sn_posts ON sn_uid = sn_friendwith
                UNION
            SELECT * FROM " . TABLE_PREFIX . "sn_posts WHERE sn_uid = $thisuser OR sn_pageid = $thisuser) as tab order by sn_date, sn_post_id DESC LIMIT $offset, $numpages
            ");

            showPosts($queryPostsFriends, "newsfeed");
            $numposts = $db->fetch_field(
                $db->write_query(" 
            SELECT count(DISTINCT(sn_post_id)) as count FROM 
                (SELECT sn_post_id, sn_pageid, sn_uid, sn_date, sn_social_post, sn_del_name 
                    FROM (SELECT sn_friendwith FROM " . TABLE_PREFIX . "sn_friends WHERE sn_uid = $thisuser) as f 
                    JOIN " . TABLE_PREFIX . "sn_posts ON sn_uid = sn_friendwith
                UNION
            SELECT * FROM " . TABLE_PREFIX . "sn_posts WHERE sn_uid = $thisuser OR sn_pageid = $thisuser) as tab
            "),
                "count"
            );
            $newsfeed_links = "<a href=\"misc.php?action=sn_newsfeedAll\">Newsfeed aller Charaktere</a> - <b>Newsfeed der Freunde</b>";
        }

        if (isset($mybb->input['sendAnswer']) && $userUseSN == 1) {
            $toPostId = intval($mybb->input['postid']);
            $thispage = getPageId($toPostId, "post");
            $answerid = getNextId("sn_answers");
            $datetime = $db->escape_string($mybb->input['sn_ansDatum'] . " " . $mybb->input['sn_ansUhrzeit']);
            $answer = $db->escape_string($mybb->input['sn_answer']);

            if (isset($_FILES['uploadImg']['name']) && $_FILES['uploadImg']['name'] != '') {
                uploadImg($answerid, "answer");
            }
            checkMentions("answer", $thispage, $thisuser, $toPostId, $answerid);
            if ($answer != '') {
                mentionUser($answer, $thispage, $toPostId, $answerid);
                savingPostOrAnswer($toPostId, $thisuser, $datetime, $answer, "sn_answers");
                redirect("misc.php?action=sn_newsfeedAll");
            } else {
                echo "<script>alert('" . $lang->socialnetwork_member_errorMessageEmpty . ".');</script>";
            }
        } else if (isset($mybb->input['sendAnswer']) && $userUseSN == 0) {
            echo "<script>alert('" . $lang->socialnetwork_member_errorNoOwnPage . ".');</script>";
        }

        if ($mybb->input['like'] == 'like') {

            $sn_postid = intval($_GET['postid']);
            $sn_ansid = intval($_GET['ansid']);

            if ($_GET['ansid'] == 0) $thispage = getPageId($sn_postid, "post");
            if ($_GET['ansid'] != 0) $thispage = getPageId($sn_ansid, "answer");

            checkMentions("like", $thispage, $thisuser, $sn_postid, $sn_ansid);
            like($thispage, $sn_postid, $sn_ansid, $thisuser, "newsfeed");
        }

        if ($mybb->input['like'] == 'dislike') {
            $sn_postid = intval($_GET['postid']);
            if ($_GET['ansid'] == 0) $thispage = getPageId($sn_postid, "post");
            if ($_GET['ansid'] != 0) $thispage = getPageId($sn_ansid, "answer");
            dislike($thispage, $sn_postid, $sn_ansid, $thisuser, "newsfeed");
        }
        $multipage = multipage($numposts, $numpages, $page, $_SERVER['PHP_SELF'] . "?action=sn_newsfeedAll");

        eval("\$outpage .= \"" . $templates->get('socialnetwork_misc_main') . "\";");
        output_page($outpage);
        die();
    }
}

$plugins->add_hook("fetch_wol_activity_end", "socialnetwork_online_activity");
/**
 * Show user in Who is Online
 * @param array info of activity of user
 * @return array acticity + info where is the user
 */
function socialnetwork_online_activity($user_activity)
{
    global $parameters, $user;
    $split_loc = explode(".php", $user_activity['location']);
    if ($split_loc[0] == $user['location']) {
        $filename = '';
    } else {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }
    switch ($filename) {
        case 'member':
            if ($parameters['area'] == "socialnetwork" && empty($parameters['site'])) {
                $user_activity['activity'] = "socialnetwork";
            }
            break;
        case 'usercp':
            if ($parameters['action'] == "socialnetwork" && empty($parameters['site'])) {
                $user_activity['activity'] = "edit_socialnetwork";
            }
            break;
    }
    return $user_activity;
}

$plugins->add_hook("build_friendly_wol_location_end", "socialnetwork_online_location");
/**
 * Build text and link for online locations
 * @param array the information we need
 */
function socialnetwork_online_location($plugin_array)
{
    global $lang;
    // print_r($plugin_array);
    $pagedata = get_user($plugin_array['user_activity']['uid']);
    $pagelink = '<a href="' . get_profile_link($pagedata['uid']) . '&amp;area=socialnetwork" >' . $pagedata['username']  . '</a>';

    if ($plugin_array['user_activity']['activity'] == "socialnetwork") {
        $socialnetwork_wol_page =  $lang->socialnetwork_wol_page;
        $lang->socialnetwork_wol_page = $lang->sprintf($socialnetwork_wol_page, $pagelink);
        $plugin_array['location_name'] = $lang->socialnetwork_wol_page;
    }
    if ($plugin_array['user_activity']['activity'] == "edit_socialnetwork") {
        $plugin_array['location_name'] = $lang->socialnetwork_wol_edit;
    }
    return $plugin_array;
}

$plugins->add_hook("modcp_start", "socialnetwork_modcp");
function socialnetwork_modcp_nav()
{
    global $db, $cache, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $modcp_nav, $socialnetwork_modcp_nav, $altbg;
    eval("\$socialnetwork_modcp_nav= \"" . $templates->get("socialnetwork_modcp_nav") . "\";");
}

/**
 * MOD CP Moderation of Social Network of users.
 * (moderators und admins) edit of the userfields
 */
$plugins->add_hook('modcp_nav', 'socialnetwork_modcp_nav');
function socialnetwork_modcp()
{
    global $mybb, $db, $cache, $lang, $templates, $theme, $headerinclude, $header, $footer, $modcp_nav;
    $lang->load("socialnetwork");
    $uid = intval($mybb->input['uid']);
    $numpages = $mybb->settings['socialnetwork_recordsperpage'];
    $usergroups_cache = $cache->read("usergroups");
    add_breadcrumb($lang->nav_modcp, "modcp.php");
    add_breadcrumb($lang->socialnetwork_modcp, "modcp.php?action=socialnetwork");

    if ($mybb->input['action'] == "socialnetwork") {
        if ($mybb->usergroup['canmodcp'] == 0) {
            error_no_permission();
        }
        $pages = intval($mybb->input['page']);

        if ($pages < 1) {
            $pages = 1;
        }
        $offset = ($pages - 1) * 10;

        $query = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "sn_users LIMIT " . $offset . ", 10 ");
        $altbg = "trow2";
        $socialnetwork_modcp_view = $lang->socialnetwork_modcp_view;
        while ($user = $db->fetch_array($query)) {
            $userdata = get_user($user['uid']);
            if ($altbg == "trow1") {
                $altbg = "trow2";
            } else {
                $altbg = "trow1";
            }
            if ($user['sn_nickname'] == "") {
                $user['sn_nickname'] = $db->fetch_field($db->simple_select("users", "username", "uid=" . $user['uid'] . ""), "username");
            }

            $user['editsnlink'] = $mybb->settings['bburl'] . "/modcp.php?action=socialnetwork_edit&amp;uid=" . $user['uid'];
            $lang->socialnetwork_modcp_view = $lang->sprintf($socialnetwork_modcp_view, $user['sn_nickname']);
            $user['username'] = format_name(htmlspecialchars_uni($user['username']), $user['usergroup']);
            $user['viewsnlink'] = get_profile_link($user['uid']) . "&amp;area=userpage";
            eval("\$socialnetwork_modcp_singleuser .= \"" . $templates->get('socialnetwork_modcp_singleuser') . "\";");
        }
        $numusers = $db->fetch_field($db->simple_select("sn_users", "COUNT(uid) AS count"), "count");
        $multipage = multipage($numusers, $numpages, $pages, $_SERVER['PHP_SELF'] . "?action=socialnetwork");

        eval("\$page = \"" . $templates->get('socialnetwork_modcp_main') . "\";");
        output_page($page);
        die();
    } elseif ($mybb->input['action'] == "socialnetwork_edit") {
        if ($mybb->usergroup['canmodcp'] == 0) {
            error_no_permission();
        }
        $pm = $mybb->settings['socialnetwork_alertpn'];
        $pm = $mybb->settings['socialnetwork_alertpn'];

        $uid = intval($mybb->input['uid']);
        $userSnData = getSnUserInfo($uid);
        $socialnetwork_modcp_edittit = $lang->socialnetwork_modcp_edittit;
        $lang->socialnetwork_modcp_edittit = $lang->sprintf($lang->socialnetwork_modcp_edittit, $userSnData['sn_nickname']);


        $sizes = get_avatit_size();
        $sn_avasizewidth = $sizes[0] . "px";
        $sn_avasizeheight = $sizes[1] . "px";
        $sn_titlesizewidth = $sizes[2] . "px";
        $sn_titlesizeheight = $sizes[3] . "px";

        $nickname = $userSnData['sn_nickname'];
        $profilbild = $userSnData['sn_avatar'];
        if ($profilbild == $db->escape_string($mybb->settings['socialnetwork_defaultavatar'])) $profilbild = "";
        $titelbild = $userSnData['sn_userheader'];
        $sn_alertPost = $userSnData['sn_alertPost'];
        $sn_alertFriend = $userSnData['sn_alertFriend'];
        $sn_alertLike = $userSnData['sn_alertLike'];
        $sn_alertMention = $userSnData['sn_alertMention'];
        $sn_alertfriendReqcheck = $userSnData['sn_alertFriendReq'];

        if ($sn_alertPost == 1) $sn_postcheck = "checked";
        else $sn_postcheck = "";
        if ($sn_alertFriend == 1) $sn_likecheck = "checked";
        else $sn_likecheck = "";
        if ($sn_alertLike == 1) $sn_friendcheck = "checked";
        else $sn_friendcheck = "";
        if ($sn_alertMention == 1) $sn_mentioncheck = "checked";
        else $sn_mentioncheck = "";
        if ($sn_alertfriendReqcheck == 1) $sn_friendReqcheck = "checked";
        else $sn_friendReqcheck = "";

        if ($pm == 1) {
            eval("\$socialnetwork_ucp_pmAlert .= \"" . $templates->get('socialnetwork_ucp_pmAlert') . "\";");
        } else {
            $socialnetwork_ucp_pmAlert = "";
        }

        $fields = getOwnFields();
        if (empty($fields)) $socialnetwork_ucp_ownFieldsBit = "Keine weiteren Felder.";
        foreach ($fields as $field) {
            $sn_fieldtitle = $field;
            $get_input  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $uid), "own_" . $field);
            eval("\$socialnetwork_ucp_ownFieldsBit .= \"" . $templates->get('socialnetwork_ucp_ownFieldsBit') . "\";");
        }

        add_breadcrumb($lang->nav_modcp, "modcp.php");
        add_breadcrumb($lang->socialnetwork_modcp, "modcp.php?action=socialnetwork");
        add_breadcrumb($lang->socialnetwork_modcp_modify);

        eval("\$page = \"" . $templates->get('socialnetwork_modcp_modify') . "\";");
        output_page($page);
        die();
    } elseif ($mybb->input['action'] == "editsn_do" && $mybb->request_method == "post") {
        if ($mybb->usergroup['canmodcp'] == 0) {
            error_no_permission();
        }

        verify_post_check($mybb->input['my_post_key']);
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

        if ($db->write_query("UPDATE " . TABLE_PREFIX . "sn_users SET
        sn_nickname='$nickname', 
        sn_avatar='$avatar', 
        sn_userheader='$titelbild',
        sn_alertPost = '$alertPost', 
        sn_alertLike='$alertLike', 
        sn_alertFriend='$alertFriend', 
        sn_alertMention ='$alertMention'
        " . $strUpdate . "
        WHERE uid = $uid")) {
            redirect("modcp.php?action=socialnetwork", $lang->socialnetwork_updated);
        } else {
            redirect("modcp.php?action=socialnetwork", $lang->socialnetwork_notupdated);
        }
    }
}

/**
 * Function to show a link to network in the postbit
 */
$plugins->add_hook("postbit", "socialnetwork_getlinkinpost");
function socialnetwork_getlinkinpost(&$post)
{
    global $mybb, $db, $lang;
    $userArray = getSnUserInfo($post['uid']);
    if ($userArray != 0) {
        $post['social_link'] = '<a href="member.php?action=profile&uid=' . $post['uid'] . '&area=socialnetwork">' . $lang->socialnetwork_sn_postlink . '</a>';
    } else {
        $post['social_link'] = "";
    }
}

/**
 * Function to show a link to network in memberlist
 */
$plugins->add_hook("memberlist_user", "socialnetwork_getlinkinmemberlist");
function socialnetwork_getlinkinmemberlist(&$user)
{
    global $mybb, $db, $lang;
    $userArray = getSnUserInfo($user['uid']);
    if ($userArray != 0) {
        $user['social_link'] = '<a href="member.php?action=profile&uid=' . $user['uid'] . '&area=socialnetwork">' . $lang->socialnetwork_sn_postlink . '</a>';
    } else {
        $user['social_link'] = "";
    }
}

/**
 * Function to show a link to network from user who is online, 
 * can be used in header 
 * here are also the links for the newsfeed defined 
 * and the informations about the last post - so you can use them 
 * in your header, if you like.
 */
$plugins->add_hook("global_start", "socialnetwork_getglobals");
function socialnetwork_getglobals()
{
    global $mybb, $db, $parser, $lang, $sn_newsfeedFriend, $sn_newsfeedAll, $sn_page, $last_post, $userinfo;
    $thisuser = intval($mybb->user['uid']);
    $userArray = getSnUserInfo($thisuser);
    $url = $mybb->settings['bburl'];
    $lang->load("socialnetwork");
    $sn_newsfeedFriend = "<a href=\"" . $url . "/misc.php?action=sn_newsfeedFriends\">" . $lang->socialnetwork_newsfeedFriends . "</a>";
    $sn_newsfeedAll = "<a href=\"" . $url . "/misc.php?action=sn_newsfeedAll\">" . $lang->socialnetwork_newsfeedAll . "</a>";
    if ($userArray != 0) {
        $sn_page =  "<a href=\"" . $url . "/member.php?action=profile&uid=" . $thisuser . "&area=socialnetwork\">" . $lang->socialnetwork_linkToOwn . "</a>";
    }
    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;

    $options = array(
        "allow_html" => $mybb->settings['socialnetwork_html'],
        "allow_mycode" => $mybb->settings['socialnetwork_mybbcode'],
        "allow_imgcode" => $mybb->settings['socialnetwork_img'],
        "filter_badwords" => $mybb->settings['socialnetwork_badwords'],
        "nl2br" => 1,
        "allow_videocode" => $mybb->settings['socialnetwork_videos'],
    );

    $get_lastpost = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "sn_posts WHERE sn_post_id = (SELECT max(sn_post_id) FROM " . TABLE_PREFIX . "sn_posts AS max)");

    if (mysqli_num_rows($get_lastpost) > 0) {
        $last_post = $db->fetch_array($get_lastpost);
        $userinfo = getSnUserInfo($last_post['sn_uid']);

        if ($userinfo == 0) {
            $userinfo['sn_nickname'] = $last_post['sn_del_name'];
            $userinfo['linkauthor'] = $last_post['sn_del_name'];
        } else {
            $userinfo['linkauthor'] = build_profile_link($userinfo['sn_nickname'], $last_post['sn_uid']);
        }
        if ($last_post['sn_social_post'] != "") {
            $last_post['sn_social_post'] = $parser->parse_message($last_post['sn_social_post'], $options);
        }
        //Do the user upload an image to the post?
        $postImg = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = " . $last_post['sn_post_id'] . " and sn_type = 'post'"));

        if (!empty($postImg)) {
            $postImgFilename = $postImg['sn_filename'];
            $last_post['sn_social_post'] .= "<br/> <img src=\"social/userimages/" . $postImgFilename . "\" style=\"width:90%\"/>";
        }
        $last_post['linktopost'] = "<a href=\"member.php?action=profile&uid=" . $last_post['sn_pageid'] . "&area=socialnetwork#" . $last_post['sn_post_id'] . "\">" . $lang->socialnetwork_linkToLastpost . "</a>";
    }

    // member.php?action=profile&uid=".$last_post['sn_pageid']."&area=socialnetwork#".$last_post['sn_post_id']."\">".$lang->socialnetwork_linkToLastpost."</a>";
    //$last_post['sn_social_post']; Postinhalt
    //$userinfo['linkauthor'] Link zum Autor
    //$last_post['linktopost'] Link zum Beitrag
    // $last_post['sn_social_post']
}
/**
 * integrate MyAlerts
 */

/**********
 *  My Alert Integration
 * *** ****/
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
     * Alert Formater for sn_Mention
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
            $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManagerAns->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkMentionFormatter($mybb, $lang, 'sn_Mention')
        );
    }

    /**
     * We need our MyAlert Formatter
     * Alert Formater for sn_Like
     */
    class MybbStuff_MyAlerts_Formatter_SocialnetworkLikeFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert4, array $outputAlert4)
        {
            $alertContent4 = $alert4->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->socialnetwork_sn_Like,
                $alertContent4['fromuser'],
                $outputAlert4['dateline']
            );
        }
        public function init()
        {
            if (!$this->lang->socialnetwork) {
                $this->lang->load('socialnetwork');
            }
        }
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert4)
        {
            $alertContent4 = $alert4->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent4['pageid'] . '&area=socialnetwork#' . $alertContent4['pid'] . '';
        }
    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        if (!$formatterManagerAns) {
            $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManagerAns->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkLikeFormatter($mybb, $lang, 'sn_Like')
        );
    }



    /**
     * We need our MyAlert Formatter
     * Alert Formater for sn_Friend
     */
    class MybbStuff_MyAlerts_Formatter_SocialnetworkFriendFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert5, array $outputAlert5)
        {
            $alertContent5 = $alert5->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->socialnetwork_sn_Friend,
                $alertContent5['fromuser'],
                $outputAlert5['dateline']
            );
        }
        public function init()
        {
            if (!$this->lang->socialnetwork) {
                $this->lang->load('socialnetwork');
            }
        }
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert5)
        {
            $alertContent5 = $alert5->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent5['pageid'] . '&area=socialnetwork';
        }
    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        if (!$formatterManagerAns) {
            $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManagerAns->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkFriendFormatter($mybb, $lang, 'sn_Friend')
        );
    }

    /**
     * We need our MyAlert Formatter
     * Alert Formater for sn_FriendRequest
     */
    class MybbStuff_MyAlerts_Formatter_SocialnetworkFriendRequestFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert6, array $outputAlert6)
        {
            $alertContent6 = $alert6->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->socialnetwork_sn_FriendRequest,
                $alertContent6['fromuser'],
                $outputAlert6['dateline']
            );
        }
        public function init()
        {
            if (!$this->lang->socialnetwork) {
                $this->lang->load('socialnetwork');
            }
        }
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert6)
        {
            $alertContent6 = $alert6->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent6['pageid'] . '&area=socialnetwork';
        }
    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
        if (!$formatterManagerAns) {
            $formatterManagerAns = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }
        $formatterManagerAns->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_SocialnetworkFriendRequestFormatter($mybb, $lang, 'sn_FriendRequest')
        );
    }
}
