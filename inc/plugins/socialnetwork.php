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
// error_reporting ( -1 );
// ini_set ( 'display_errors', true );

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
                                                        <a href=\"index.php?module=tools-socialnetwork\" style=\"margin: 10px;\">".$lang->socialnetwork_infoacp."</a> | 
                                                        <img src=\"styles/default/images/icons/custom.png\" alt=\"\" style=\"margin-left: 10px;\" /><a href=\"index.php?module=config-settings&amp;action=change&amp;gid=" . (int)$set['gid'] . "\" style=\"margin: 10px;\">".$lang->socialnetwork_infoolddata."</a><hr style=\"margin-bottom: 5px;\"></div>";
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
        `sn_id_user` int(10) NOT NULL AUTO_INCREMENT,
        `sn_uid` int(20) NOT NULL,
        `sn_nickname` varchar(200) NOT NULL,
        `sn_avatar` varchar(200) NOT NULL,
        `sn_description` varchar(200) NOT NULL,    
    PRIMARY KEY (`sn_id_user`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");

    // `sn_lovestatus` varchar(200) NOT NULL,
    // `sn_job` varchar(200) NOT NULL,
    // `sn_living` varchar(200) NOT NULL,
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
        'socialnetwork_alertpn' => array(
            'title' => 'Private Nachricht?',
            'description' => $lang->socialnetwork_settings_pn,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 7
        ),
        'socialnetwork_alertAlert' => array(
            'title' => 'MyAlert?',
            'description' => $lang->socialnetwork_settings_alert,
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 8
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

function socialnetwork_uninstall()
{
    global $db;
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
}

function socialnetwork_activate()
{
    //Hier fügen wir nur die Variablen ein, die das Plugin sichtbar machen - so bleiben eigene Änderungen beim deaktivieren/aktivieren erhalten.
    //add variables
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$userstars}') . "#i", '{$userstars}{$social_link}');


    //finde replacement stuff
    // $keeptemplates = intval($mybb->settings['socialnetwork_templates']);
    // if($keeptemplates == 1) {
    //     //just add the main variables
    // } else {
    //     //add all variables and templates 
    // }

}

function socialnetwork_deactivate()
{
    //Template Variablen entfernen
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$social_link}') . "#i", '');
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
        "template" => 'UCP mainpageverwaltung',
        "sid" => "-2",
        "version" => "1.0",
        "dateline" => TIME_NOW
    );
    $template[4] = array(
        "title" => 'socialnetwork_ucp_nav',
        "template" => 'UCP Navigation',
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
        $socialnetwork_options[] = $form->generate_check_box('can_sn', 1, $lang->socialnetwork_perm_base, array('checked' => $mybb->input['can_sn']));
        $socialnetwork_options[] = $form->generate_check_box('can_snedit', 1, $lang->socialnetwork_perm_edit, array('checked' => $mybb->input['can_snedit']));
        $socialnetwork_options[] = $form->generate_check_box('can_snmod', 1, $lang->socialnetwork_perm_mod, array('checked' => $mybb->input['can_snmod']));

        $form_container->output_row($lang->socialnetwork_perm, '', '<div class="group_settings_bit">' . implode('</div><div class="group_settings_bit">', $socialnetwork_options) . '</div>');
    }
}

/*
 * Fügt die Verwaltung des Social Networks ins UCP Menü ein 
 */
$plugins->add_hook("usercp_menu", "socialnetwork_usercp_menu");
function socialnetwork_usercp_menu()
{
    global $templates;
    eval("\$socialnetwork_ucp_nav .= \"" . $templates->get("socialnetwork_ucp_nav") . "\";");
    $templates->cache["usercp_nav_misc"] = str_replace("<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">", "<tbody style=\"{\$collapsed['usercpmisc_e']}\" id=\"usercpmisc_e\">{$socialnetwork_ucp_nav}", $templates->cache["usercp_nav_misc"]);
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
//TODO Verwaltung UCP


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
