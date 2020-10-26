<?php
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
global $mybb, $lang, $db;

$page->add_breadcrumb_item("Soziales Netzwerk - Administration", "index.php?module=tools-socialnetwork");
//$page->output_header("Relations - Administration");
/**
 * Set Defaultvalues for categories
 */
if(!$mybb->input['action']) {
    if($mybb->request_method == "post")
    {
        if(isset($mybb->input['do_setDefault']))
        {
            $default_cat = $db->escape_string($mybb->get_input('socialnetwork_fields', MyBB::INPUT_STRING));
            // $db->query("UPDATE " . TABLE_PREFIX . "users SET rela_cat = '" . $default_cat . "'");
            // admin_redirect("index.php?module=tools-relations");
        }
    }

    $page->output_header("Testausgabe");

    $sub_tabs['setDefault'] = array(
        'title' => "Social Network",
        'link' => "index.php?module=tools-socialnetwork",
        'description' => "Hier kannst du die Default Felder neu erstellen."
    );

    $page->output_nav_tabs($sub_tabs, 'setDefault');

    $form = new Form("index.php?module=tools-relations", "post");

    $form_container = new FormContainer("Soziales Netzwerk Verwaltung");
    $form_container->output_row_header("Defaultwerte setzen");
    $form_container->output_row_header("Kategorien", array('width' => 350));
    $form_container->output_row_header("&nbsp;");

    $form_container->output_cell("<label>Achtung</label> <div class=\"description\">Bitte mit Vorsicht benutzen. 
    Wenn du die Defaults änderst wird Inhalt von nicht mehr verwendeten Feldern gelöscht!
    Anwendung: Kategorien mit , getrennt ins Textfeld schreiben. Achtung, vorne und hinten muss auch ein Komma stehen.<br/>
<b>Beispiel:</b> ,Familie,Freunde,Liebe,Bekannte,Ungemocht,Sonstiges,</div>");

    $form_container->output_cell($form->generate_text_box("socialnetwork_fields", "", array('style' => 'width: 250px;')));
    $form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_setDefault")));
    $form_container->construct_row();

    $form_container->end();
    $form->end();
    $page->output_footer();
}





