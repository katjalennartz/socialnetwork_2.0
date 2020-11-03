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
//    error_reporting ( -1 );
//    ini_set ( 'display_errors', true );

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
  		`sn_del_nutzername` varchar(300) DEFAULT NUll,
  		`sn_del_avatar` varchar(30) DEFAULT NUll,
	PRIMARY KEY (`sn_post_id`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //create table for answers
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_answer` (
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
            'title' => 'HTML?',
            'description' => $lang->socialnetwork_settings_html,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 1
        ),
        'socialnetwork_mybbcode' => array(
            'title' => 'MyBB Code?',
            'description' => $lang->socialnetwork_settings_mybbcode,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 2
        ),
        'socialnetwork_img' => array(
            'title' => 'Bilder?',
            'description' => $lang->socialnetwork_settings_img,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 3
        ),
        'socialnetwork_badwords' => array(
            'title' => 'Bad Words?',
            'description' => $lang->socialnetwork_settings_badwords,
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 4
        ),
        'socialnetwork_videos' => array(
            'title' => 'Videos?',
            'description' => $lang->socialnetwork_settings_video,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 5
        ),
        'socialnetwork_defaultavatar' => array(
            'title' => 'Defaultavatar?',
            'description' => $lang->socialnetwork_settings_defavatar,
            'optionscode' => 'text',
            'value' => 'https://', // Default
            'disporder' => 6
        ),
        'socialnetwork_avasize' => array(
            'title' => 'Avatargröße?',
            'description' => $lang->socialnetwork_settings_avasize,
            'optionscode' => 'text',
            'value' => '150,150', // Default
            'disporder' => 7
        ),
        'socialnetwork_titlesize' => array(
            'title' => 'Titelbildgröße?',
            'description' => $lang->socialnetwork_settings_titlesize,
            'optionscode' => 'text',
            'value' => '600,180', // Default
            'disporder' => 8
        ),
        'socialnetwork_alertpn' => array(
            'title' => 'Private Nachricht?',
            'description' => $lang->socialnetwork_settings_pn,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 9
        ),
        'socialnetwork_alertAlert' => array(
            'title' => 'MyAlert?',
            'description' => $lang->socialnetwork_settings_alert,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 10
        ),
        'socialnetwork_uploadImg' => array(
            'title' => 'Bilder in Posts?',
            'description' => 'Dürfen Mitglieder Bilder für Posts hochladen?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 11
        ),
        'socialnetwork_uploadImgSize' => array(
            'title' => 'Dateigröße?',
            'description' => 'Wie groß dürfen Bilder sein (Dateigröße)?',
            'optionscode' => 'text',
            'value' => '200000', // Default
            'disporder' => 12
        ),
        'socialnetwork_uploadImgWidth' => array(
            'title' => 'erlaubte Breite?',
            'description' => 'Wie breit dürfen Bilder höchstens sein?',
            'optionscode' => 'text',
            'value' => '400', // Default
            'disporder' => 13
        ),
        'socialnetwork_uploadImgHeight' => array(
            'title' => 'erlaubte Höhe?',
            'description' => 'Wie hoch dürfen Bilder höchstens sein?',
            'optionscode' => 'text',
            'value' => '200', // Default
            'disporder' => 14
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
}

function socialnetwork_activate()
{
    //Hier fügen wir nur die Variablen ein, die das Plugin sichtbar machen - so bleiben eigene Änderungen beim deaktivieren/aktivieren erhalten.
    //add variables
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$userstars}') . "#i", '{$userstars}{$social_link}');
}

function socialnetwork_deactivate()
{
    //Template Variablen entfernen
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$social_link}') . "#i", '');
}

function socialnetwork_uninstall()
{
    global $db, $cache;
    if ($db->table_exists("sn_users")) $db->drop_table("sn_users");
    if ($db->table_exists("sn_posts"))  $db->drop_table("sn_posts");
    if ($db->table_exists("sn_answer")) $db->drop_table("sn_answer");
    if ($db->table_exists("sn_friends")) $db->drop_table("sn_friends");
    if ($db->table_exists("sn_likes")) $db->drop_table("sn_likes");

    $db->delete_query("templates", "title LIKE 'socialnetwork_%'");
    $db->delete_query("templategroups", "prefix = 'socialnetwork'");

    //SETTINGS ENTFERNEN!
    $db->delete_query('settings', "name LIKE 'socialnetwork%_'");
    $db->delete_query('settinggroups', "name = 'socialnetwork'");
    //CSS löschen
    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'socialnetwork.css'");
    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }
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
        <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->viewinguserpage}</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        {$headerinclude}
        </head>
        <body>
        {$header}
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
                    <div class="col-3 sn_links">
                        <div class="sn_logo"><img src="social/logo_150px.png" alt="social logo"/></div>
                      {$socialnetwork_member_infobit}
                    </div>
                    <div class="col-8 sn_rechts">
                      <fieldset>
                         <legend>Beitrag erstellen</legend>
                         <input type="date" value="2017-08-01" name="datum" /> <input type="time" name="sn_uhrzeit" /><br />
                         <textarea id="sn_post" name="sn_post" rows="4" cols="50"> </textarea><br />
                        <input type="submit" name="senden" value="senden">
                    </fieldset>
                    </div>
                  </div>
                </div>				
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
    $template[1] = array(
        "title" => 'socialnetwork_member_posts',
        "template" => 'this is the post',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[2] = array(
        "title" => 'socialnetwork_member_answerbit',
        "template" => 'the answers ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[2] = array(
        "title" => 'socialnetwork_member_friends',
        "template" => 'the answers ',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[3] = array(
        "title" => 'socialnetwork_ucp_main',
        "template" => '<html>
        <head>
        <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->viewinguserpage}</title>
        <link rel="stylesheet" href="social/css/bootstrap.min.css">
        {$headerinclude}
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
                    <div class="col-3 sn_links">
                        <div class="sn_logo"><img src="social/logo_150px.png" alt="social logo"/></div>
                      {$socialnetwork_member_infobit}
                    </div>
                    <div class="col-8">
                    <div class="sn_rechts">
                      <fieldset>
                         <legend>Beitrag erstellen</legend>
                        <!--action=profile&uid=7&area=socialnetwork-->
                         <form method="post" enctype="multipart/form-data" name="picform" id="picform" method="post"> <!--ohne action?-->
                        <!--<form name="uploadformular" enctype="multipart/form-data" action="dateiupload.php" method="post">-->	 
                         <input type="date" value="2017-08-01" name="datum" /> <input type="time" name="sn_uhrzeit" value="12:00" /><br />
                         <textarea id="sn_post" name="sn_post" rows="4" cols="50"> </textarea><br />
                        <input type="file" name="uploadImg" size="60" maxlength="255"><br />
                        <input class="sn_send" type="submit" name="sendPost" value="senden">
                         </form>
                        </fieldset>
                        </div>
                        {$socialnetwork_member_postbit}
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
        "template" => '{$own_title}: {$own_value}',
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
                <input type="date" value="2017-08-01" name="sn_ansDatum" /> <input type="time" name="sn_ansWhrzeit" value="12:00" /><br />
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

    //socialnetwork_ucp_ownFieldsBit
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
        $sn_avasizewidth = $sizes[0];
        $sn_avasizeheight = $sizes[1];
        $sn_titlesizewidth = $sizes[2];
        $sn_titlesizeheight = $sizes[3];

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
    global $db, $mybb, $lang, $templates, $cache, $page, $headerinclude, $header, $footer, $usercpnav, $theme, $socialnetwork_member_postbit;
    $lang->load('socialnetwork');
    $usergroups_cache = $cache->read("usergroups");

    echo ("this page:" . $mybb->input['uid']);
    if ($mybb->input['area'] == "socialnetwork") {
        //not allowed to use social network
        if (!$usergroups_cache[$mybb->user['usergroup']]['socialnetwork_isallowed']) {
            error_no_permission();
        }
        //getsettings
        $defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);
        //get sizes
        $sizes = get_avatit_size();
        $sn_avasizewidth = $sizes[0] + 10; // +10 for nice view with padding
        $sn_avasizeheight = $sizes[1] + 10; // +10 for nice view with padding
        $sn_titlesizewidth = $sizes[2];
        $sn_titlesizeheight = $sizes[3];

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
        //Now we want the individual fields
        $fields = getOwnFields();
        // $socialnetwork_ucp_ownFieldsBit
        foreach ($fields as $field) {
            $sn_fieldtitle = $field;
            $get_value  = $db->fetch_field($db->simple_select("sn_users", "own_" . $field, "uid = " . $sn_activepage['uid']), "own_" . $field);
            $own_title = $field;
            if ($get_value == "") $own_value = "keine Angabe";
            else $own_value = $get_value;
            eval("\$socialnetwork_member_infobit .= \"" . $templates->get('socialnetwork_member_infobit') . "\";");
        }

        if (isset($mybb->input['sendPost'])) {
            //check if theres an upload
            if (isset($_FILES['uploadImg']['name']) && $_FILES['uploadImg']['name'] != '') {
                uploadImg();
            }
            savePost();
            // redirect('member.php?action=profile&uid='.$sn_activepage['uid'].'&area=socialnetwork');
        }
        if (isset($mybb->input['sendAnswer'])) {
            saveAnswer();
        }
        showPosts();
        eval("\$page = \"" . $templates->get('socialnetwork_member_main') . "\";");
        output_page($page);
        die();
    }
}


//TODO POSTEN + edit + delete

//TODO ANTWORTEN +edit + delete

//TODO LIKE

//TODO FREUNDE 
//TODO ADD
//TODO DELETE
//TODO Bestätigen

//TODO MYAlert Integration



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
function uploadImg()
{
    global $db, $mybb;

    $uploadImgWidth = $mybb->settings['socialnetwork_uploadImgWidth'];
    $uploadImgHeight = $mybb->settings['socialnetwork_uploadImgHeight'];
    $maxfilesize = $mybb->settings['socialnetwork_uploadImgSize'];

    $post = $db->fetch_field($db->write_query("Select max(sn_post_id) as max FROM " . TABLE_PREFIX . "sn_posts LIMIT 1"), "max");
    //echo($post);
    if ($post == "") $post = 1;
    $imgpath = "social/userimages/";
    // Check if gallery path is writable
    if (!is_writable('social/userimages/')) echo ("Der Pfad ist nicht beschreibar. Bitte sag dem Admin bescheid.");

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
    if ($sizes === false) echo ("Mit den Größen des Bildes stimmt etwas nicht.");
    elseif ((!empty($uploadImgWidth) && $sizes[0] >  $uploadImgWidth) || (!empty($uploadImgHeight) && $sizes[1] > $uploadImgHeight)) {
        //Delete the temp file
        @unlink($_FILES['uploadImg']['tmp_name']);
        echo ("Fehler Größe");
    } else {
        $filesize = $_FILES['uploadImg']['size'];

        if (!empty($maxfilesize) && $filesize > $maxfilesize) {
            //Delete the temp file
            @unlink($_FILES['uploadImg']['tmp_name']);
            echo ("Fehler mit Dateigröße.");
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

        $filename = $mybb->user['uid'] . '_' . date('d_m_y_g_i_s') . '.' . $extension;

        if ($failed == false) move_uploaded_file($_FILES['uploadImg']['tmp_name'], $imgpath . $filename);
        else 
        rename($_FILES['uploadImg']['tmp_name'], $imgpath . $filename);

        @chmod($imgpath . $filename, 0644);

        $db->write_query("INSERT INTO " . TABLE_PREFIX . "sn_imgs
						(sn_filesize, sn_filename, sn_width, sn_height, sn_uid, sn_postId)
						VALUES ( $filesize,'$filename', $sizes[0], $sizes[1], " . $mybb->user['uid'] . ", $post)");
    }
}
/**
 * Show Posts and Answers
 */
function showPosts()
{
    global $db, $mybb, $templates, $socialnetwork_member_postbit, $socialnetwork_member_answerbit;
    $activepage = intval($mybb->input['uid']);
    $thisUser = intval($mybb->user['uid']);
    $queryPosts = $db->simple_select("sn_posts", "*", "sn_pageid = $activepage");
    while ($get_post = $db->fetch_array($queryPosts)) {
        $postuser = intval($get_post['sn_uid']);
        $name = $db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = '$postuser'"), "sn_nickname"); 
        if ($name == "") $name = $db->fetch_field($db->simple_select("users", "username", "uid = '$postuser'"), "username");
        $sn_postname = '<a href="member.php?action=profile&uid=' . $postuser . '&area=socialnetwork">' . $name . '</a>';
        $sn_postimg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$postuser'"), "sn_avatar");
        $sn_date = date('d.m.y - H:i', strtotime($get_post['sn_date']));
        $sn_showPost = $get_post['sn_social_post'];
        $sn_postid = intval($get_post['sn_post_id']);

        $sn_ansFormImg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$thisUser'"), "sn_avatar");
        $socialnetwork_member_answerbit="";
        $queryAnswer = $db->simple_select("sn_answer", "*", "sn_post_id = $sn_postid");
            while($get_answer = $db->fetch_array($queryAnswer)){
                
                $sn_ansUser = intval($get_answer['sn_uid']);
                echo("bla".$sn_postid);
                $sn_anspostimg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$sn_ansUser'"), "sn_avatar");
                $ansname = $db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = '$sn_ansUser'"), "sn_nickname"); 
                
                if ($ansname == "") $ansname = $db->fetch_field($db->simple_select("users", "username", "uid = '$sn_ansUser'"), "username");
                $sn_ansname = '<a href="member.php?action=profile&uid=' . $sn_ansUser . '&area=socialnetwork">' . $ansname . '</a>';
                $sn_ansdate = date('d.m.y - H:i', strtotime($get_answer['sn_date']));
                $sn_showAnswer = $get_answer['sn_answer'];  

            //     <div class="sn_answer">
            //     <fieldset>
            //         <img class="sn_ansProfilbild" src="{$sn_anspostimg}" alt="" />
            //         <span class="sn_ansName">{$sn_ansname}</span>
            //         <span class="sn_ansDate">{$sn_ansdate}</span>
            //         <span class="sn_edit">{$edit} {$delete}</span>
            //         <div class="sn_socialAnswer">{$sn_showAnswer}</div>
            //         <hr>
            //     </fieldset>
            // </div>

                eval("\$socialnetwork_member_answerbit .= \"" . $templates->get('socialnetwork_member_answerbit') . "\";");
            }
        eval("\$socialnetwork_member_postbit .= \"" . $templates->get('socialnetwork_member_postbit') . "\";");
    }
}

/**
 * Save a Post to database
 */
function savePost()
{
    global $db, $mybb;
    //get the infos
    $datetime = $db->escape_string($mybb->input['datum'] . " " . $mybb->input['sn_uhrzeit']);
    $post = $db->escape_string($mybb->input['sn_post']);
    $activepage = intval($mybb->input['uid']);
    $thisUser = intval($mybb->user['uid']);
    $insert_array = array(
        "sn_pageid" =>  $activepage,
        "sn_uid" => $thisUser,
        "sn_date" => $datetime,
        "sn_social_post" => $post,
    );
    $db->insert_query("sn_post", $insert_array);
}

/**
 * Save Answer to database
 */
function saveAnswer(){
    global $db, $mybb;
    //get the infos
    $datetime = $db->escape_string($mybb->input['sn_ansDatum'] . " " . $mybb->input['sn_ansWhrzeit']);
    $answer = $db->escape_string($mybb->input['sn_answer']);
    $postId = intval($mybb->input['postid']);
    $thisUser = intval($mybb->user['uid']);
    $insert_array = array(
        "sn_post_id" =>  $postId,
        "sn_uid" => $thisUser,
        "sn_date" => $datetime,
        "sn_answer" => $answer,
    );
    $db->insert_query("sn_answer", $insert_array);
}


/**** Helper Functions  ******/
/** get own fields and return an array with cleaned names */
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
//get sizes of Avatar and Title from settings of admin, return Array with sizes
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
