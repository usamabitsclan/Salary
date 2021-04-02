<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Salary
Description: Generate Salary
Version: 2.3.0
Requires at least: 2.3.*
*/

define('SALARY_MODULE_NAME', 'salary');

//hooks()->add_action('admin_init','app_init_opportunity_profile_tabs');

$CI = &get_instance();
/**
 * Register activation module hook
 */
register_activation_hook(SALARY_MODULE_NAME, 'salary_activation_hook');

function salary_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(SALARY_MODULE_NAME, [SALARY_MODULE_NAME]);
//add_option('Car_Module', '[]');


hooks()->add_action('admin_init', 'salary_init_menu_items');


hooks()->add_action('admin_init', 'salary_permissions');
function salary_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('salary', $capabilities, _l('salary'));
}

function salary_init_menu_items(){
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('custom-menu-unique-id', [
        'name'     => 'Salary', // The name if the item
        'href'     => admin_url('salary'), // URL of the item
        'position' => 46, // The menu position, see below for default positions.
        'icon'     => 'fa fa-question-circle', // Font awesome icon
    ]);
}
/**
 * Load the module helper
 */
$CI->load->helper(SALARY_MODULE_NAME . '/salary');
// $CI = &get_instance();

// $CI->load->library(SALARY_MODULE_NAME . '/' . 'pdf/App_pdf');
// $CI->load->library(SALARY_MODULE_NAME . '/' . 'libraries/pdf/salary_pdf');


function opportunity_menu_item_collapsible()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('custom-menu-unique-id', [
        'name'     => 'Car Module', // The name if the item
        'collapse' => true, // Indicates that this item will have sub items
        'position' => 47, // The menu position
        'icon'     => 'fa fa-question-circle', // Font awesome icon
    ]);

    // The first paremeter is the parent menu ID/Slug
    $CI->app_menu->add_sidebar_children_item('custom-menu-unique-id', [
        'slug'     => 'child-to-custom-menu-item', // Required ID/slug UNIQUE for the child menu
        'name'     => 'Sub Menu', // The name if the item
        'href'     => 'https://perfexcrm.com/car_module/Car_Module', // URL of the item
        'position' => 5, // The menu position
        'icon'     => 'fa fa-exclamation', // Font awesome icon
    ]);
}

hooks()->add_action('clients_init', 'my_module_clients_area_menu_items');

function my_module_clients_area_menu_items()
{
    // Item for all clients
    add_theme_menu_item('unique-item-id', [
        'name'     => 'Custom Clients Area',
        'href'     => site_url('my_module/acme'),
        'position' => 10,
    ]);

    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('unique-logged-in-item-id', [
            'name'     => 'Only Logged In',
            'href'     => site_url('my_module/only_logged_in'),
            'position' => 15,
        ]);
    }
}
