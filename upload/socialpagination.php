<?php
define("IN_MYBB", 1);

//  error_reporting ( -1 );
//  ini_set ( 'display_errors', true ); 

require("global.php");
global $db, $mybb, $templates, $lang, $parser, $socialnetwork_member_postbit,$socialnetwork_member_postimg_ans, $socialnetwork_member_answerbit, $socialnetwork_member_postimg;

$options = array(
    "allow_html" => $mybb->settings['socialnetwork_html'],
    "allow_mycode" => $mybb->settings['socialnetwork_mybbcode'],
    "allow_imgcode" => $mybb->settings['socialnetwork_img'],
    "filter_badwords" => $mybb->settings['socialnetwork_badwords'],
    "nl2br" => 1,
    "allow_videocode" => $mybb->settings['socialnetwork_videos'],
); // "me_username" => $memprofile['username'],

$thisuser = intval($mybb->user['uid']);
$defaultava = $db->escape_string($mybb->settings['socialnetwork_defaultavatar']);


$pageno = intval($_POST['pageno']);
$pageid = intval($_POST['pageid']);
$thispage = intval($_POST['pageid']);
//TODO Variable machen settingfeld einfügen! 
$no_of_records_per_page = 5;
$offset = ($pageno - 1) * $no_of_records_per_page;

$cnt_likes_post = "";

$nextposts = $db->query("SELECT * FROM mybb_sn_posts WHERE sn_pageid =  " . $pageid . " ORDER by sn_date DESC LIMIT $offset, $no_of_records_per_page");

while ($get_post = $db->fetch_array($nextposts)) {
    $likevar = "like";
    $sn_like = $lang->socialnetwork_member_like;
    //show the image beside the anwser form
    $sn_ansFormImg = $db->fetch_field($db->simple_select("sn_users", "sn_avatar", "uid = '$thisuser'"), "sn_avatar");
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
    if (($thisuser == $postuser) || ($mybb->usergroup['canmodcp'] == 1)) {
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
        if ($likesarray['sn_uid'] == $thisuser) {
            $likevar = "dislike";
            $sn_like = $lang->socialnetwork_member_dislike;
        }
    }
    //count likes
    $cnt_likes_post = $db->fetch_field($db->simple_select("sn_likes", "count(*) as cnt", "sn_postid = $sn_postid"), "cnt");

    //Do the user upload an image to the post?
    if (!empty($postImg)) {
        $postImgFilename = $postImg['sn_filename'];
        $postImgId = $postImg['sn_imgId'];

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
            if ($likesarray['sn_uid'] == $thisuser) {
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
        $socialnetwork_member_postimg_ans = "";
        $postImgAns = $db->fetch_array($db->simple_select("sn_imgs", "*", "sn_postId = $ansid and sn_type = 'answer'"));
        if (!empty($postImgAns)) {
            $postImgFilename = $postImgAns['sn_filename'];
            $postImgId = $postImgAns['sn_imgId'];

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
        eval("\$socialnetwork_member_answerbit .= \"" . $templates->get('socialnetwork_member_answerbit') . "\";");
    }

    eval("\$showMorePosts .= \"" . $templates->get('socialnetwork_member_postbit') . "\";");
}
echo $showMorePosts;

