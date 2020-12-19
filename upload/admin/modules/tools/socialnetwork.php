<?php
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
global $mybb, $lang, $db;
$lang->load("socialnetwork");
$page->add_breadcrumb_item("Soziales Netzwerk - Administration", "index.php?module=tools-socialnetwork");

if (!$mybb->input['action']) {

    //get alle existing columns in table sn_users
    $columns = $db->write_query("SHOW COLUMNS FROM " . TABLE_PREFIX . "sn_users WHERE field LIKE 'own_%'"); //
    $oldfields= array();
    $oldfieldsstring = "";
    //save them in an array and save a string - without the prefix own_
    while($column = $db->fetch_array($columns)) {
        array_push($oldfields, $column['Field']);
        $oldfieldsstring .= $column['Field'].",";
    }
     $oldfieldsstring = substr($oldfieldsstring, 0, -1);
     $oldfieldsstring = str_replace('own_', '',$oldfieldsstring);

    //what we do after the button is clicked
    if ($mybb->request_method == "post") {
        //we don't want it empty
        if (isset($mybb->input['do_setSNField'])) {
            //array for the input
            $fields = array();
            //Get the input
            $get_fieldinput = $db->escape_string($mybb->get_input('socialnetworkfields'));
            //be sure there no spaces, we don't want them!
            $get_fieldinput = str_replace(" ", "", $get_fieldinput);
            //and we want to get an array
            $fields = explode(',', $get_fieldinput);
            
            //take our array and test if we had to add a column or not
            foreach ($fields as $field_own) {
                if (!$db->field_exists("own_".$field_own, "sn_users")) {   
                    $db->write_query("ALTER TABLE ".TABLE_PREFIX."sn_users
                    ADD `own_".$field_own."` varchar(255);");
                }
            }
            //take the old ones and compare it to the new ones, so we can test if we have to delete columns
            foreach($oldfields as $oldfield){
                if(!in_array($oldfield,$fields)){
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

    //make the containers, input and button
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
