# Soziales Network 2.0  
von Risuena  
Kontakt: Discord: risuena#5389
Download: https://github.com/katjalennartz/socialnetwork_2.0  
LICENSE: GNU General Public License v3.0  
**Last Update: 30.03.2023**

## wichtiges Update        
* **update script durchfüren**: -> update_sn_march23.php              
* Neue Settings: Anzeige von Mentions auf eigener Seite Ja / Nein       
* Neues DB Feld in sn_anwers         
* Neue Templates socialnetwork_member_shortinfos und socialnetwork_member_shortinfos_nopage (wird in update script hinzugefügt) 
          
* Neue Darstellung von Infos auf member Seite bei wunsch: (Variable dafür siehe nächster abschnitt)       

## Wichtige Variablen.  

* Member Profil in member_profile:  
{$sn_page_profil}  - link zur SN Seite       
{$socialnetwork_member_shortinfos} - Shortinfos anzeigen (Freunde, letzter geschriebener Beitrag, Letzter post auf Seite etc) **muss manuell hinzugefügt werden**


* Global verwendbar:  
{$sn_newsfeedFriend} - link zum Newsfeed der Freunde  
{$sn_newsfeedAll} - link zum Newsfeed (alle Beiträge)  
{$sn_page} - Link zum Userprofil (vom user der online ist)   

* Letzter Post(global):  
{$last_post['sn_social_post']} Postinhalt    
{$userinfo['linkauthor']} Link zum Autor  
{$last_post['linktopost']} Link zum Beitrag  
{$last_post['sn_social_post']}  Post  
     
    
* Mitglieder Liste - einbinden in memberlist_user:  
{$user['social_link']} - link zum Profil des Nutzers  

* Postbit:  
{$post['social_link']}  
## Wichtige links:

member.php?action=profile&uid=X&area=socialnetwork  (ersetze X mit uid des users)

misc.php?action=sn_newsfeedAll. //feed mit allen posts
misc.php?action=sn_newsfeedFriends //feed mit Posts von Freunden

usercp.php?action=socialnetwork  //Link zum UCP

## Installation
1. Plugn installieren
2. CHMOD Rechte von social/userimages evt. auf 755 setzen. (z.b über ein FTP Programm)
3. optional: Daten übertragen bei Bedarf (boardadresse/social_saveold.php)
4. social_saveold.php löschen
5. Gruppen Berechtigungen überprüfen und einmal speichern. 
6. Felder anlegen, die ausgefüllt werden sollen/können
7. Einstellungen vornehmen. (Benachrichtigung per PN oder Alert etc.)
8. Bei Bedarf weitere Variablen einfügen (Mitgliederliste, Postbit, Globale sind per Default NICHT eingebunden)
9. Font Awseome einbinden, falls nicht sowieso schon eingebunden.
  
## Inhalt
### Inhalt:  
admin/modules/tools/socialnetwork.php  
inc/languages/deutsch_du/socialnetwork.lang.php  
inc/languages/deutsch_du/admin/socialnetwork.lang.php  
inc/plugins/socialnetwork.php  
social/js/jquery.inview.js  
social/js/script.js  
social/logo.png  
social/profil_leer.png  
social_saveold.php  
social/userimages  (ACHTUNG! CHMOD RECHTE AUF 755)  
socialpagination.php  
  
  
### Templates:   
Gruppe: Soziales Netzwerk  
socialnetwork_member_answerbit  
socialnetwork_member_answeredit  
socialnetwork_member_friends  
socialnetwork_member_friendsbit  
socialnetwork_member_friendsbitAsked  
socialnetwork_member_friendsbitToAccept  
socialnetwork_member_infobit  
socialnetwork_member_main  
socialnetwork_member_postbit  
socialnetwork_member_postedit  
socialnetwork_misc_answerbit  
socialnetwork_misc_main  
socialnetwork_misc_postbit  
socialnetwork_misc_postimg  
socialnetwork_modcp_main  
socialnetwork_modcp_modify  
socialnetwork_modcp_nav  
socialnetwork_modcp_singleuser  
socialnetwork_ucp_main  
socialnetwork_ucp_nav  
socialnetwork_ucp_ownFieldsBit  
socialnetwork_ucp_pmAlert  
**seit märz 2023:**              
socialnetwork_member_shortinfos         
socialnetwork_member_shortinfos_nopage
     
### CSS  
socialnetwork.css  
