function change($id,$date,$time) {
    $pid = 'p' + $id;
    $post = document.getElementById($pid).innerHTML;
    document.getElementById($pid).innerHTML = '<form action="" method="post">'
    + '<textarea name ="editPost" class='+$pid+' id='+$pid+'>'+$post+'</textarea><br />'
    +'<input type="hidden" value="'+$id+'" name="sn_postEditId" />'
    +'<input type="date" value="'+$date+'" name="sn_postDatumEdit" />'
    +'<input type="time" name="sn_postUhrzeitEdit" value="'+$time+'" /></br>'
    +'<input type="submit" value="speichern" name="saveEditPost"/>'
    +'<input type="button" value="abbrechen" onclick="abort(' + $id + ',\'' + $post + '\')"/></form>';
}

function abort($id, $post) {
    $pid = 'p' + $id;
    document.getElementById($pid).innerHTML = $post;
}

function changeAns($id,$date,$time){
    $aid = 'a' + $id;
    $ans = document.getElementById($aid).innerHTML;
    document.getElementById($aid).innerHTML = '<form action="" method="post">' 
    + '<textarea name="editAnswer" class=' +$aid +' id=' +$aid +'>'+$ans+'</textarea><br />'
    + '<input type="hidden" value="'+$id+'" name="sn_ansEditId" />'
    + '<input type="date" value="'+$date+'" name="sn_ansDatumEdit" />'
    + '<input type="time" name="sn_ansUhrzeitEdit" value="'+$time+'" /></br>'
    + '<br /><input type="submit" value="speichern" name="saveEditAns"/>'
    + '<input type="button" value="abbrechen" onclick="abortAns('+$id+',\''+$ans+'\')\"/></form>';
}
function abortAns($id,$ans){
    $aid = 'a' + $id;
    document.getElementById($aid).innerHTML = $ans;
}

function addImg($type, $postid){
    $pid = $type + $postid;
    console.log("pidaddimg" + $pid)
    $saveInnerhtml = document.getElementById($pid).innerHTML; 
    document.getElementById($pid).innerHTML = '<form enctype="multipart/form-data" name="picform" id="picform" method="post">'
    + '<input type="file" name="uploadImg" size="60" maxlength="255">' 
    + '<input type="hidden" name="'+$type+'id" value="'+$postid+'">'
    + '<input class="sn_send" type="submit" name="saveImg'+$type+'" value="speichern">'
    +'</form>'
    + '<input type="button" value="abbrechen" onclick="abortImg(\''+$pid+'\',\''+$postid+'\',\''+$type+'\')\"/>';
}   
function abortImg($pid,$postid,$type){
    console.log($pid)
    document.getElementById($pid).innerHTML = ' '
    + '<button onClick="addImg(\''+$type+'\',\''+$postid+'\')" class="editDelete">'
    + '<i class="fas fa-camera-retro"></i>'
    + '</button>'
}

/*handle of infinite scrolling - load data when reach end of page*/
$(document).ready(function(){

    $('#loader').on('inview', function(event, isInView) {
      
        if (isInView) {
            var nextPage = parseInt($('#pageno').val())+1;
            var pageId = parseInt($('#thispage').val());
            
            $.ajax({
                type: 'POST',
                url: 'socialpagination.php',
                data: { pageno: nextPage, pageid: pageId},
                success: function(data){
                    if(data != ''){							 
                        $('#posts').append(data);
                        $('#pageno').val(nextPage);
                       
                    } else {								 
                        $("#loader").hide();
                    }
                }
            });
        }
    });
});

