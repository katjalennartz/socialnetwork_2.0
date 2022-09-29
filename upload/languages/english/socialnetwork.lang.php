<?php
/**
 * Social Network 1.0 Main Language File
 */

$l['socialnetwork_view'] = "{1}'s social network";
$l['socialnetwork_view2'] = "social network";
$l['socialnetwork_view_newsfeedAll'] = "Newsfeed social network";
$l['socialnetwork_usercp'] = "social network";
$l['socialnetwork_change'] = "edit the social network profile";
$l['socialnetwork_save'] = "save";
$l['socialnetwork_updated'] = "success: the social network Profil was saved";
$l['socialnetwork_notupdated'] = "couldn't save. something went wrong.";

$l['socialnetwork_ucp_link'] = "view social network";

$l['socialnetwork_ucp_alertPost'] = "when new post or answer";
$l['socialnetwork_ucp_alertLike'] = "when someone likes a post.";
$l['socialnetwork_ucp_alertFriend'] = "when someone wants to be your friend";
$l['socialnetwork_ucp_alertMention'] = "when you where mentioned in a post.";
$l['socialnetwork_ucp_alertFriendReq'] = "when someone react to your friendrequest.";

$l['socialnetwork_deletepost'] = "Do you really want to delete this? This can't be undone.";
$l['socialnetwork_deletefriend'] = "Do you really want to delete this friend? This can't be undone.";

//Memberpage
$l['socialnetwork_member_ownNotFilled'] = "not specified";
$l['socialnetwork_member_errorMessageEmpty'] = "you can't post an empty post";
$l['socialnetwork_member_errorNoOwnPage'] = "you only can write a post, if you've got an own social page.";
$l['socialnetwork_member_errorMessageDelete'] = "can't delete.";
$l['socialnetwork_member_ownNotFilled'] = "not specified";
$l['socialnetwork_error_nopage'] = "There no data for this user in the social network.";
$l['socialnetwork_error_nopage_title'] = "no social network";

//Like/Dislike fontawesome! 
$l['socialnetwork_member_like'] = '<i class="far fa-heart"></i>';
$l['socialnetwork_member_dislike'] = '<i class="fas fa-heart"></i>';

//Friends
$l['socialnetwork_member_delete'] = '<i class="fas fa-user-minus"></i>';
$l['socialnetwork_member_openRequestFriendTit'] = '<h1 class="friends">open requests</h1>';
$l['socialnetwork_member_openRequestFriendAskedTit'] = '<h1 class="friends">requested</h1>';
$l['socialnetwork_member_openRequestFriendAskedOtherPage'] = '<span class="allreadyAsked">allready requested</span>';
$l['socialnetwork_member_openRequestFriendAskedOwnPage'] = '<span class="allreadyAsked">request to</span>';
$l['socialnetwork_member_toFriendNotAllowed'] = "you only are allowed to add user as friends, if you're habe a own social network page.";

//Upload Image
$l['socialnetwork_upload_errorPath'] = "the path is not writable, please contact the administrator";
$l['socialnetwork_upload_errorSizes'] = "the size of the image are to big.";
$l['socialnetwork_upload_errorFileSize'] = "sie filesize of the image is to big.";


// Private Message Alerts
$l['socialnetwork_pm_postSubject'] = 'social network: new post.';
$l['socialnetwork_pm_post']= '{1} has posted a <a href="member.php?action=profile&uid={2}&area=socialnetwork#{3}">post</url> on your social network page.';

$l['socialnetwork_pm_answerSubject'] = 'social network: new answer';
$l['socialnetwork_pm_answer']= '{1} has postet a new <a href="member.php?action=profile&uid={2}&area=socialnetwork#ans{3}">answer</url> at your social network page.';

$l['socialnetwork_pm_likeSubject'] = 'social network: Like.';
$l['socialnetwork_pm_like']= '{1} liked this <a href="member.php?action=profile&uid={2}&area=socialnetwork#{3}">post</url>.';

$l['socialnetwork_pm_mentionSubject'] = 'social network: Mention.';
$l['socialnetwork_pm_mention']= '{1} has mentioned you in this <a href="member.php?action=profile&uid={2}&area=socialnetwork#{3}">post</url>.';

$l['socialnetwork_pm_friendSubject'] = 'social network: friendrequest.';
$l['socialnetwork_pm_friend']= '{1} hast sent you a friendrequest. you can confirm on your <a href="member.php?action=profile&uid={2}&area=socialnetwork">page</a>.';


$l['socialnetwork_pm_friendReqSubject'] = 'Social Network: reaction to your friendrequest.';
$l['socialnetwork_pm_friendReqAcpt']= '{1} accepted your request.';
$l['socialnetwork_pm_friendReqDeny']= '{1} denied your request.';

// WHO IS ONLINE
$l['socialnetwork_wol_page'] = "viewing {1}'s social network.";
$l['socialnetwork_wol_edit'] = 'editing his social network page.';

// ALERTS
$l['socialnetwork_sn_Post'] = '{1} had posted on your social network page.';
$l['myalerts_setting_sn_Post'] = 'social network: alert, new social network post.';

$l['socialnetwork_sn_Answer'] = '{1} had answered on one of your social network posts.';
$l['myalerts_setting_sn_Answer'] = 'social network: alert, new answer.';

$l['socialnetwork_sn_Like'] = '{1} liked a one of your posts or answers.';
$l['myalerts_setting_sn_Like'] = 'social network: alert, new like.';

$l['socialnetwork_sn_Friend'] = '{1} sent you a social network friend request .';
$l['myalerts_setting_sn_Friend'] = 'social network: alert, new friend request.';

$l['socialnetwork_sn_Mention'] = '{1} has mentioned you in a social network post or answer.';
$l['myalerts_setting_sn_Mention'] = 'social network: alert, when you were mentioned.';

$l['socialnetwork_sn_FriendRequest'] = '{1} reacted to your friendrequest.';
$l['myalerts_setting_sn_FriendRequest'] = 'social network: alert, when someone reacted to your friend request.';

//LINKS
$l['socialnetwork_sn_postlink'] = 'social network';
$l['socialnetwork_newsfeedFriends'] = 'newsfeed friends';
$l['socialnetwork_newsfeedAll'] = 'newsfeed all';
$l['socialnetwork_linkToOwn'] = 'social network';
$l['socialnetwork_linkToLastpost'] = 'go to post';


//MODERATION
$l['socialnetwork_modcp_edittit'] = "edit {1}'s social network";
$l['socialnetwork_modcp_nav'] = 'social network';
$l['socialnetwork_modcp'] = 'social network';
$l['socialnetwork_modcp_tit'] = 'social network ModCP';
$l['socialnetwork_modcp_edit'] = 'edit';
$l['socialnetwork_modcp_view'] = 'view social network from {1}';

?>
