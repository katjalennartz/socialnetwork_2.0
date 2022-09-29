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

zu css hinzufügen für tooltip bei gelikten posts:
```
        /* Tooltip  likes  */
        .sn_tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
        }

        /* Tooltip text */
        .sn_tooltip .sn_tooltiptext {
            visibility: hidden;
            background-color: black;
            color: #fff;
            text-align: center;
            padding: 5px 5px;
            border-radius: 6px;
            position: absolute;
            z-index: 1;
            top: 12px;
            white-space: nowrap;
        }

        .sn_tooltiptext a:after{
            content: ", ";
        }
        
        .sn_tooltiptext a:last-child:after{
            content: "";
        }

        .sn_tooltiptext a {
            display: inline-block;
            padding: 0 2px;
        }
        
        /* Show the tooltip text when you mouse over the tooltip container */
        .sn_tooltip:hover .sn_tooltiptext {
        visibility: visible;
        }
        
        
        
```
