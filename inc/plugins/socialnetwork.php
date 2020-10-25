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
    global $lang;
    $lang->load("socialnetwork");

    return array(
        "name" => $lang->userpages_title,
        "description" => $lang->userpages_desc,
        "website" => "https://lslv.de/risu",
        "author" => "risuena",
        "authorsite" => "https://lslv.de/risu",
        "version" => "2.0",
        "compatability" => "18*"
    );
}

function socialnetwork_is_installed()
{
    global $db;
    return $db->field_exists("sn_users", "sn_id_user");
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
        `sn_lovestatus` varchar(200) NOT NULL,
        `sn_job` varchar(200) NOT NULL,
        `sn_living` varchar(200) NOT NULL,
        `sn_description` varchar(200) NOT NULL,    
    PRIMARY KEY (`sn_id_user`)
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
            'description' => 'Dürfen Mitglieder HTML verwenden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 1
        ),
        'socialnetwork_mybbcode' => array(
            'title' => 'MyBB Code?',
            'description' => 'Dürfen Mitglieder MyBB Code verwenden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 2
        ),
        'socialnetwork_img' => array(
            'title' => 'Bilder?',
            'description' => 'Dürfen Mitglieder Bilder verwenden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 3
        ),
        'socialnetwork_badwords' => array(
            'title' => 'Bad Words?',
            'description' => 'Sollen \'bad words\' gefiltert werden?',
            'optionscode' => 'yesno',
            'value' => '0', // Default
            'disporder' => 4
        ),
        'socialnetwork_videos' => array(
            'title' => 'Videos?',
            'description' => 'Dürfen Mitglieder Videos verwenden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 5
        ),
        'socialnetwork_defaultavatar' => array(
            'title' => 'Defaultavatar?',
            'description' => 'Adresse zu einem Defaultavatar, wenn der Nutzer keins ausgewählt hat oder gelöscht worden ist.',
            'optionscode' => 'text',
            'value' => 'https://', // Default
            'disporder' => 6
        ),
        'socialnetwork_alertpn' => array(
            'title' => 'Private Nachricht?',
            'description' => 'Benachrichtigung der Mitglieder (bei Post, Antwort, Like, Freundanfrage etc) per PN?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 7
        ),
        'socialnetwork_alertAlert' => array(
            'title' => 'MyAlert?',
            'description' => 'Benachrichtigung der Mitglieder (bei Post, Antwort, Like, Freundanfrage etc) per MyAlert(Plugin muss installiert sein!)?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 7
        ),
        'socialnetwork_templates' => array(
            'title' => 'Templates behalten?',
            'description' => 'Beim Deaktivieren, die Templates behalten und nicht löschen? -> Praktisch bei großen Änderungen, aber vorsicht bei Updates vom Plugin!',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 7
        )
    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }
    rebuild_settings();
}

function socialnetwork_uninstall()
{
}

function socialnetwork_activate()
{
    socialnetwork_addtemplates();
    //add variables

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
    global $db, $mybb, $lang;
    $keeptemplates = intval($mybb->settings['socialnetwork_templates']);
    if ($keeptemplates == 1) {
        //just delete the main variables
    } else {
        //delete all variables and templates 
    }
}
/** 
 * add templates
 * */
function socialnetwork_addtemplates() {
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
}
/**
 * Gruppenberechtigungen
 */
$plugins->add_hook("admin_formcontainer_end", "socialnetwork_editgroup");
function socialnetwork_editgroup()
{
	global $run_module, $form_container, $lang, $form, $mybb, $user;
	
	$lang->load("socialnetwork");

	if($run_module == 'user' && !empty($form_container->_title) && !empty($lang->users_permissions) && $form_container->_title == $lang->users_permissions)
	{
		$socialnetwork_options = array();
		$socialnetwork_options[] = $form->generate_check_box('can_sn', 1, $lang->userpages_perm_base, array('checked' => $mybb->input['canuserpage']));
		$socialnetwork_options[] = $form->generate_check_box('can_snedit', 1, $lang->userpages_perm_edit, array('checked' => $mybb->input['canuserpageedit']));	
		$socialnetwork_options[] = $form->generate_check_box('can_snmod', 1, $lang->userpages_perm_mod, array('checked' => $mybb->input['canuserpagemod']));

		$form_container->output_row($lang->userpages_perm, '', '<div class="group_settings_bit">'.implode('</div><div class="group_settings_bit">', $socialnetwork_options).'</div>');
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
