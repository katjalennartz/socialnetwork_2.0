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

var nextPage = parseInt($('#pageno').val())+1;


/*handle of infinite scrolling - load data when reach end of page*/
$(document).ready(function(){
    $('#loader').on('inview', function(event, isInView) {
        if (isInView) {
            var nextPage = parseInt($('#pageno').val())+1;
            var pageId = parseInt($('#activepage').val());
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

