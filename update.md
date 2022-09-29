-> 29.09.2022
Vorm löschen eines Posts erst eine Bestätigung nötig:   

templateänderung: socialnetwork_member_postedit   
Inhalt ersetzen mit: 

```
<button class="editDelete" name="editpost" id="edit{$sn_postid}" onclick="change({$sn_postid},'{$sn_date_date}','{$sn_date_time}')" ><i class="fas fa-pen"></i></button>
<a href="member.php?action=profile&uid={$thispage}&area=socialnetwork&postdelete={$sn_postid}" onClick="return confirm('{$lang->socialnetwork_deletepost');" class="editDelete" >
<i class="fas fa-trash"></i>
</a>'
```
