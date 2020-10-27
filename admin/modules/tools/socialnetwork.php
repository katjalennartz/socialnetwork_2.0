<?php
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
global $mybb, $lang, $db;
$lang->load("socialnetwork");
$page->add_breadcrumb_item("Soziales Netzwerk - Administration", "index.php?module=tools-socialnetwork");

if (!$mybb->input['action']) {

    $columns = $db->write_query("SHOW COLUMNS FROM " . TABLE_PREFIX . "sn_users WHERE field LIKE 'own_%'"); //
    $oldfields= array();
    $oldfieldsstring = ",";
    while($column = $db->fetch_array($columns)) {
        array_push($oldfields, str_replace('own_', '',$column['Field']));
        $oldfieldsstring .= $column['Field'].",";
    }
    $oldfieldsstring = str_replace('own_', '',$oldfieldsstring);

    if ($mybb->request_method == "post") {
        if (isset($mybb->input['do_setSNField'])) {
            
            $fields = array();

            $get_fieldinput = $db->escape_string($mybb->get_input('socialnetworkfields'));
            $fields = explode(',', $get_fieldinput);
            //clean array
            array_shift($fields);
            array_pop($fields);
 
            foreach ($fields as $field_own) {
                echo $field_own." - in for each - <br>";
                if (!$db->field_exists("own_".$field_own, "sn_users")) {   
                    $db->write_query("ALTER TABLE ".TABLE_PREFIX."sn_users
                    ADD `own_".$field_own."` varchar(255);");
                }
            }
            foreach($oldfields as $oldfield){
                if(!in_array($oldfield,$fields)){
                    echo(" - !in_array".$oldfield);
                     $db->write_query("ALTER TABLE ".TABLE_PREFIX."sn_users
                     DROP COLUMN `own_".$oldfield."`");
                }
            }
            admin_redirect("index.php?module=tools-socialnetwork");
        }
    }

    $page->output_header("Verwaltung Soziales Netzwerk");

    $sub_tabs['do_setSNField'] = array(
        'title' => $lang->socialnetwork_module_title,
        'link' => "index.php?module=tools-socialnetwork",
        'description' => $lang->socialnetwork_module_descr
    );

    $page->output_nav_tabs($sub_tabs, 'do_setSNField');

    $form = new Form("index.php?module=tools-socialnetwork", "post");

    $form_container = new FormContainer($lang->socialnetwork_module_contitle);
    $form_container->output_row_header("Erklärung");
    $form_container->output_row_header("Felder hinzufügen", array('width' => 400));
    $form_container->output_row_header("&nbsp;");

    $form_container->output_cell($lang->socialnetwork_module_explanation);
    $form_container->output_cell($form->generate_text_box("socialnetworkfields", $oldfieldsstring, array('style' => 'width: 250px;')));
    $form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_setSNField")));
    $form_container->construct_row();


    $form_container->end();
    $form->end();

    

    $page->output_footer();
    
}
