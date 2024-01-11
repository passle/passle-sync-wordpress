<?php 

namespace Passle\PassleSync\Services;

class TaxonomyRegistryService 
{
    public static function init() 
    {   
        add_action("init", [static::class, "create_taxonomy"]);
    }

    public static function create_taxonomy() 
    {
        $labels = array(
            "name" => "Passle Tag Groups",
            "singular_name" => "Passle Tag Group",
            "search_items" => "Search Passle Tag Groups",
            "all_items" => "All Passle Tag Groups",
            "edit_item" => "Edit Passle Tag Group",
            "update_item" => "Update Passle Tag Group",
            "add_new_item" => "Add new Passle Tag Group",
            "new_item_name" => "New Passle Tag Group name",
            "menu_name" => "Passle Tag Group",
            "not_found" => "No Passle Tag Groups Found"
        );

        $args = array(
            "hierarchical" => false,
            "labels" => $labels,
            "show_ui" => true,
            "show_admin_column" => true,
            "query_var" => true,
            "meta_box_cb" => null,
            "rewrite" => array("slug" => "tag_group")
        );

        register_taxonomy("tag_group", array(PASSLESYNC_POST_TYPE), $args);
    }
}