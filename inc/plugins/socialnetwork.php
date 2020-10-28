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
//   error_reporting ( -1 );
//   ini_set ( 'display_errors', true );

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
    //Erstellt Tabelle für die userdaten
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

    //Erstellt Tabelle für die Posts
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_post` (
		`sn_post_id` int(20) NOT NULL AUTO_INCREMENT,
        `sn_pageid` int(20) NOT NULL,
		`sn_uid` int(20) NOT NULL,
  		`sn_social_post` varchar(300) NOT NULL,
  		`sn_del_username` varchar(300) DEFAULT NUll,
  		`sn_del_nutzername` varchar(300) DEFAULT NUll,
  		`sn_del_avatar` varchar(30) DEFAULT NUll,
	PRIMARY KEY (`sn_post_id`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //Erstellt Tabelle für die Antworten
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

    //Erstellt Tabelle für die Freunde
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_friends` (
        `sn_friendsid` int(20) NOT NULL AUTO_INCREMENT,
  	    `sn_uid` int(20) NOT NULL,
  	    `sn_friendwith` int(20) NOT NULL,
  	    `sn_accepted` int(1) NOT NULL DEFAULT 0,
  	PRIMARY KEY (`sn_friendsid`)
	) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    //Erstellt Tabelle für Likes.
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "sn_likes` (
        `sn_like_id` int(11) NOT NULL AUTO_INCREMENT,
        `sn_postid` int(11) NOT NULL,
        `sn_answerid` int(11) NOT NULL,
        `sn_uid` varchar(200) NOT NULL,
        PRIMARY KEY (`sn_like_id`)
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
            'value' => 'https://', // Default
            'disporder' => 7
        ),
        'socialnetwork_titlesize' => array(
            'title' => 'Titelbildgröße?',
            'description' => $lang->socialnetwork_settings_titlesize,
            'optionscode' => 'yesno',
            'value' => '1', // Default
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
        // 'socialnetwork_templates' => array(
        //     'title' => 'Templates behalten?',
        //     'description' => 'Beim Deaktivieren, die Templates behalten und nicht löschen? -> Praktisch bei großen Änderungen, aber vorsicht bei Updates vom Plugin!',
        //     'optionscode' => 'yesno',
        //     'value' => '1', // Default
        //     'disporder' => 7
        // )
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
    if ($db->table_exists("sn_users")) {
        $db->drop_table("sn_users");
    }
    if ($db->table_exists("sn_post")) {
        $db->drop_table("sn_post");
    }
    if ($db->table_exists("sn_answer")) {
        $db->drop_table("sn_answer");
    }
    if ($db->table_exists("sn_friends")) {
        $db->drop_table("sn_friends");
    }
    if ($db->table_exists("sn_likes")) {
        $db->drop_table("sn_likes");
    }

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
        "template" => '<div class="socialmain">All the html stuff and other variables</html>',
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
        "title" => 'socialnetwork_member_answer',
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
        <title>{$lang->user_cp} - {$lang->socialnetwork_usercp}</title>
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
                <label>Nickname:</label> <input type="text" name="nickname" value="{$nutzername}"/><br />
                <label>Avatar:</label> <input type="text" name="profilbild" value="{$profilbild}"/><br />
                <label>Titelbild:</label> <input type="text" name="titelbild" value="{$titelbild}"/><br />
                
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
            display: flex;
            display: -webkit-flex;
            -moz-display: flex;
            flex-wrap: wrap;
            -moz-flex-wrap: wrap;
            -webkit-flex-wrap: wrap;
            justify-content: flex-start;
            -moz-justify-content: flex-start;
            -webkit-justify-content: flex-start;
        }
        
        .ucp_social legend{
            font-weight: bold;
        }
        
        .ucp_social label{
            display: block;
            width: 120px;
            float: left; 
            clear: left;
        }
        
        .ucp_social input{
            margin: 2px;
        }                           ',
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
* //WIP Verwaltung UCP
*	This function handles everything related to the user Cp
*/
$plugins->add_hook("usercp_start", "socialnetwork_usercp");
function socialnetwork_usercp()
{
    global $db, $mybb, $lang, $cache, $templates, $page, $theme, $headerinclude, $header, $footer, $usercpnav;
    $lang->load('socialnetwork');
    $thisUser = $mybb->user['uid'];

    $linktosocial = '<span class="smalltext"><a href="member.php?action=profile&uid=' . $mybb->user['uid'] . '&area=socialnetwork">' . $lang->socialnetwork_ucp_link . '</a></span>';
    $usergroups_cache = $cache->read("usergroups");
    if ($mybb->input['action'] == "socialnetwork") {
        add_breadcrumb($lang->nav_usercp, "usercp.php");
        add_breadcrumb($lang->changeuserpage, "usercp.php?action=socialnetwork");

        //user is not allowed to use social network
        if (!$usergroups_cache[$mybb->user['usergroup']]['socialnetwork_isallowed'] || !$usergroups_cache[$mybb->user['usergroup']]['socialnetwork_canedit']) {
            error_no_permission();
        }

        //get the inputs and settings of user
        $nickname = $db->fetch_field($db->simple_select("sn_users", "sn_nickname", "uid = " . $mybb->user['uid']), "sn_nickanme");
        $userheader = $db->fetch_field($db->simple_select("sn_users", "sn_userheader", "uid = " . $mybb->user['uid']), "sn_userheader");
        $useravatar = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = " . $mybb->user['uid']), "sn_avatar");

        $alertPost  = $db->fetch_field($db->simple_select("sn_users", "sn_alertPost", "uid = " . $mybb->user['uid']), "sn_alertPost");
        $alertFriend  = $db->fetch_field($db->simple_select("sn_users", "sn_alertFriend", "uid = " . $mybb->user['uid']), "sn_alertFriend");
        $alertLike  = $db->fetch_field($db->simple_select("sn_users", "sn_alertLike", "uid = " . $mybb->user['uid']), "sn_alertLike");
        $alertMention  = $db->fetch_field($db->simple_select("sn_users", "sn_alertMention", "uid = " . $mybb->user['uid']), "sn_alertMention");

        $fields = getOwnFields();
        if (empty($fields)){
            $socialnetwork_ucp_ownFieldsBit ="Keine weiteren Felder.";
        }
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
        $strOwnFields =""; 
        $strownIns=""; 
        $strUpdate = "";
        //are there own fields? 
        if (!empty($ownfields)) {
            //we need some strings to make our query work
            $strownIns = ",";
            $strOwnFields =",";
            $strUpdate =",";
            //and now we have to puzzle
            foreach ($ownfields as $ownfield) {                
                $strOwnFields .= "own_".$ownfield.",";
                $strownIns .= "'".$db->escape_string($mybb->input[$ownfield])."',";
                $inputvalue = $db->escape_string($mybb->input[$ownfield]);
                $strUpdate.= "own_".$ownfield."='".$inputvalue."',"; 
            }
            $strOwnFields=substr($strOwnFields, 0, -1);
            $strownIns=substr($strownIns, 0, -1);
            $strUpdate=substr($strUpdate, 0, -1);
        }

        $db->write_query("INSERT INTO " . TABLE_PREFIX . "sn_users(uid, sn_nickname, sn_avatar, sn_userheader, sn_alertPost, sn_alertFriend,sn_alertLike,sn_alertMention".$strOwnFields.") 
        VALUES 
        ('$thisUser', '$nickname','$avatar','$titelbild','$alertPost','$alertFriend','$alertLike','$alertMention'" . $strownIns . ") 
        ON DUPLICATE KEY UPDATE 
        sn_nickname='$nickname', sn_avatar='$avatar', sn_userheader='$titelbild', 
        sn_alertPost = '$alertPost', sn_alertLike='$alertLike', sn_alertFriend='$alertFriend', sn_alertMention ='$alertMention'
        ".$strUpdate."");
        
        redirect('usercp.php?action=relas_usercp');
    }
}

//TODO Anzeige Profil

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

/**** Helper Functions  ******/
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
