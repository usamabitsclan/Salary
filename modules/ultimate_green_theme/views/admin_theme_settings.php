<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Show options for customers area theme enabled / disabled
 */
$enabled = get_option('ultimate_green_theme_customers'); ?>

<div class="form-group">
    <label for="ultimate_green_theme_customers" class="control-label clearfix">
        <?= _l('Enable Green Theme for your customers?<br><small>- Click to change
</small>'); ?>
    </label>
    <hr>
    <div class="radio radio-primary radio-inline">
        <input 
        type="radio" 
        id="y_opt_1_ultimate_green_theme_customers_enabled" 
        name="settings[ultimate_green_theme_customers]" 
        value="1" <?= ($enabled == '1') ?' checked' : '' ?>
        >
        <label for="y_opt_1_ultimate_green_theme_customers_enabled">
            <?= _l('settings_yes'); ?>
        </label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input 
        type="radio" 
        id="y_opt_2_admin-green_theme_enabled" 
        name="settings[ultimate_green_theme_customers]" 
        value="0" <?= ($enabled == '0') ?' checked' : '' ?>
        >
        <label for="y_opt_2_admin-green_theme_enabled">
            <?= _l('settings_no'); ?>
        </label>
    </div>
</div>