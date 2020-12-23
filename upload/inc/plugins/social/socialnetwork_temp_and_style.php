<?php

/**
 * social network for mybb Plugin
 * FILE FOR ADDING TEMPLATES AND STYLES
 * @author risuena
 * @version 2.0
 * @copyright risuena 2020
 * 
 */

 /** 
 * add templates
 * */

 // Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
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
        .socialmain.container{
            color:#000;
        }
        
        .socialmain a{
            color:#000;
        }
        
        .sn_socialPost img {
            max-width: 100%;
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
            margin-top: -10px;
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
