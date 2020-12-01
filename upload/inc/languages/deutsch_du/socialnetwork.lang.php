<?php
/**
 * Social Network 1.0 Main Language File
 */

$l['socialnetwork_view'] = "{1}'s soziales Netzwerk";
$l['socialnetwork_usercp'] = "soziales Netzwerk";
$l['socialnetwork_change'] = "bearbeite dein Profil vom Sozialen Netzwerk";
$l['socialnetwork_save'] = "speichern";
$l['socialnetwork_updated'] = "dein soziales Netzwerk Profil wurde erfolgreich gespeichert";
$l['socialnetwork_notupdated'] = "Entschuldige, irgendwas ist beim Speichern deines Profils schief gegangen.";

$l['socialnetwork_ucp_link'] = "Soziales Netzwerk ansehen";

$l['socialnetwork_ucp_alertPost'] = "bei neuem Post oder Antwort.";
$l['socialnetwork_ucp_alertLike'] = "wenn jemanden ein Post oder eine Antwort von dir gefällt.";
$l['socialnetwork_ucp_alertFriend'] = "wenn jemand dein Freund sein will.";
$l['socialnetwork_ucp_alertMention'] = "wenn dich jemand in einem Post erwähnt.";
$l['socialnetwork_ucp_alertFriendReq'] = "wenn jemand auf deine Freundschaftsanfrage reagiert hat.";

//Memberpage
$l['socialnetwork_member_ownNotFilled'] = "keine Angabe";
$l['socialnetwork_member_errorMessageEmpty'] = "Du kannst keinen leeren Beitrag setzen";
$l['socialnetwork_member_errorNoOwnPage'] = "Du kannst nur Posten, wenn du selbst eine Social Network Seite hast.";
$l['socialnetwork_member_errorMessageDelete'] = "Löschen nicht möglich.";

//Like/Dislike fontawesome! 
$l['socialnetwork_member_like'] = '<i class="far fa-heart"></i>';
$l['socialnetwork_member_dislike'] = '<i class="fas fa-heart"></i>';

//Friends
$l['socialnetwork_member_delete'] = '<i class="fas fa-user-minus"></i>';
$l['socialnetwork_member_openRequestFriendTit'] = '<h1 class="friends">Offene Anfragen</h1>';
$l['socialnetwork_member_openRequestFriendAskedTit'] = '<h1 class="friends">Angefragt</h1>';
$l['socialnetwork_member_openRequestFriendAskedOtherPage'] = '<span class="allreadyAsked">schon angefragt</span>';
$l['socialnetwork_member_openRequestFriendAskedOwnPage'] = '<span class="allreadyAsked">angefragt bei</span>';
$l['socialnetwork_member_toFriendNotAllowed'] = 'Du kannst Freunde nur hinzufügen, wenn du selbst das Social Network nutzt.';

//Upload Image
$l['socialnetwork_upload_errorPath'] = "Der Pfad ist nicht beschreibar, wende dich an einen Admin.";
$l['socialnetwork_upload_errorSizes'] = "Dein Bild entspricht nicht den erlaubten Maßen.";
$l['socialnetwork_upload_errorFileSize'] = "Die Dateigröße des Bildes ist zu groß.";


// Private Message Alerts
$l['socialnetwork_pm_postSubject'] = 'Social Network: Neuer Post.';
$l['socialnetwork_pm_post']= '{1} hat einen <a href="member.php?action=profile&uid={2}&area=socialnetwork#{3}">Post</url> auf deiner Userpage geschrieben.';

$l['socialnetwork_pm_answerSubject'] = 'Social Network: Neue Antwort';
$l['socialnetwork_pm_answer']= '{1} hat eine neue <a href="member.php?action=profile&uid={2}&area=socialnetwork#ans{3}">Post</url> auf deiner Userpage geschrieben.';

$l['socialnetwork_pm_likeSubject'] = 'Social Network: Like.';
$l['socialnetwork_pm_like']= '{1} gefällt dein <a href="member.php?action=profile&uid={2}&area=socialnetwork#{3}">Beitrag</url>.';

$l['socialnetwork_pm_mentionSubject'] = 'Social Network: Mention.';
$l['socialnetwork_pm_mention']= '{1} hat dich in einem <a href="member.php?action=profile&uid={2}&area=socialnetwork#{3}">Beitrag</url> erwähnt.';

$l['socialnetwork_pm_friendSubject'] = 'Social Network: Freundschaftsanfrage.';
$l['socialnetwork_pm_friend']= '{1} möchte mit dir befreundet sein. Gehe auf deine <a href="member.php?action=profile&uid={2}&area=socialnetwork">Seite</a> um ihn zu bestätigen.';


$l['socialnetwork_pm_friendReqSubject'] = 'Social Network: Reaktion auf Freundschaftsanfrage.';
$l['socialnetwork_pm_friendReqAcpt']= '{1} hat deine Freundschaftsanfrage akzeptiert';
$l['socialnetwork_pm_friendReqDeny']= '{1} hat deine Freundschaftsanfrage abgelehnt';

// WHO IS ONLINE
$l['socialnetwork_wol_page'] = 'Sieht sich das Social Network von {1} an.';
$l['socialnetwork_wol_edit'] = 'Bearbeitet die eigene Social Network Seite.';

// ALERTS
$l['socialnetwork_sn_Post'] = '{1} hat einen Post in deinem Social Network gepostet.';
$l['myalerts_setting_sn_Post'] = 'Soziales Netzwerk: Benachrichtigung, bei neuem Post auf deiner Seite.';

$l['socialnetwork_sn_Answer'] = '{1} hat auf einen Social Network Post geantwortet.';
$l['myalerts_setting_sn_Answer'] = 'Soziales Netzwerk: Benachrichtigung, bei neuer Antwort auf einen deiner Posts.';

$l['socialnetwork_sn_Like'] = '{1} gefällt ein Post oder eine Antwort von dir beim Social Network.';
$l['myalerts_setting_sn_Like'] = 'Soziales Netzwerk: Benachrichtigung, wenn jemandem ein Post oder eine Antwort von dir gefällt.';

$l['socialnetwork_sn_Friend'] = '{1} will mit dir im Social Network befreundet sein.';
$l['myalerts_setting_sn_Friend'] = 'Soziales Netzwerk: Benachrichtigung, wenn jemand mit dir befreundet sein will.';

$l['socialnetwork_sn_Mention'] = '{1} hat dich in einem Post oder einer Antwort erwähnt.';
$l['myalerts_setting_sn_Mention'] = 'Soziales Netzwerk: Benachrichtigung, wenn du in einem Post erwähnt wirst.';

$l['socialnetwork_sn_FriendRequest'] = '{1} hat auf deine Freundschaftsanfrage reagiert.';
$l['myalerts_setting_sn_FriendRequest'] = 'Soziales Netzwerk: Benachrichtigung, wenn jemand auf deine Freundschaftsanfrage reagiert hat.';

//MODERATION
$l['socialnetwork_modcp_edittit'] = 'Soziales Netzwerk von {1} bearbeiten';
$l['socialnetwork_modcp_nav'] = 'Soziales Netzwerk';
$l['socialnetwork_modcp'] = 'Soziales Netzwerk';
$l['socialnetwork_modcp_tit'] = 'Soziales Netzwerk ModCP';
$l['socialnetwork_modcp_edit'] = 'bearbeiten';
$l['socialnetwork_modcp_view'] = 'Soziales Netzwerk von {1} ansehen';



?>
