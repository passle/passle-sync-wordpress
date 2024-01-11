<?php 

namespace Passle\PassleSync\Services;

class TaxonomyRegistryService 
{
    public static function init() 
    {   
        add_action("init", [static::class, "create_taxonomy"]);
        add_filter('term_row_actions', 'disable_taxonomy_term_editing', 10, 3);
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
            "menu_name" => "Passle Tag Groups",
            "not_found" => "No Passle Tag Groups Found"
        );

        $args = array(
            "hierarchical" => false,
            "labels" => $labels,
            "show_ui" => true,
            "show_admin_column" => true,
            "query_var" => true,
            "meta_box_cb" => null,
            "rewrite" => array("slug" => PASSLESYNC_TAG_GROUP_TAXONOMY)
        );

        register_taxonomy(PASSLESYNC_TAG_GROUP_TAXONOMY, array(PASSLESYNC_POST_TYPE), $args);
    }

    public static function disable_taxonomy_term_editing($actions, $taxonomy, $tag) 
    {
        if ($taxonomy === PASSLESYNC_TAG_GROUP_TAXONOMY) {
            unset($actions["edit"]);
            unset($actions["inline hide-if-no-js"]);
        }
        return $actions;
    }
}