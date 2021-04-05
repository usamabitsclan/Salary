<?php
/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: HRM module
Description: HRM module
Author: Bitsclan Solutions
Author URI: https://bitsclan.com
Version: 1.0.0
Requires at least: 2.4.4
*/
define('HRM_MODULE_NAME', 'hrm');

$CI = &get_instance();
/**
 * Register activation module hook
 */
register_activation_hook(HRM_MODULE_NAME, 'hrm_activation_hook');

function hrm_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(HRM_MODULE_NAME, [HRM_MODULE_NAME]);


$CI->load->helper(HRM_MODULE_NAME . '/hrm');


hooks()->add_action('admin_init', 'hrm_permissions');

function hrm_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('hrm', $capabilities, _l('hrm'));
}

hooks()->add_action('admin_init', 'hrm_init_menu_items');

function hrm_init_menu_items(){
    $CI = &get_instance();

    if (has_permission('hrm', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('hrm', [
            'name'     => _l('hrm'), // The name if the item
            'collapse' => true, // Indicates that this item will have submitems
            'position' => 57, // The menu position
            'icon'     => 'fa fa-history', // Font awesome icon
        ]);

        $CI->app_menu->add_sidebar_children_item('hrm', [
            'slug'     => 'hrm-salary', // Required ID/slug UNIQUE for the child menu
            'name'     => _l('salary'), // The name if the item
            'href'     => admin_url('hrm/salary'), // URL of the item
            'position' => 1, // The menu position
            'icon'     => '', // Font awesome icon
        ]);
    }
}

