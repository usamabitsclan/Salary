<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Ultimate Green Theme
Description: Ultimate Green Theme for Perfex CRM
Version: 1.0.0
Author: Hélder Valentim
Author URI: https://dweb.digital
Requires at least: 2.3.2
*/

define('ULTIMATE_green_THEME_MODULE_NAME', 'ultimate_green_theme');
define('ULTIMATE_green_THEME_CSS', module_dir_path(ULTIMATE_green_THEME_MODULE_NAME, 'assets/css/theme_styles.css'));

$CI = &get_instance();
register_activation_hook(ULTIMATE_green_THEME_MODULE_NAME, 'ultimate_green_theme_activation_hook');

function ultimate_green_theme_activation_hook()
{
	require(__DIR__ . '/install.php');
}

$CI->load->helper(ULTIMATE_green_THEME_MODULE_NAME . '/ultimate_green_theme');
