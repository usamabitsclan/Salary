<?php
defined('BASEPATH') or exit('No direct script access allowed');


/**
 * Check whether column exists in a table
 * Custom function because Codeigniter is caching the tables and this is causing issues in migrations
 * @param  string $column column name to check
 * @param  string $table table name to check
 * @return boolean
 */

function get_timesheets_option($name)
{
	$CI = & get_instance();
	$options = [];
    $val  = '';
    $name = trim($name);
    

    if (!isset($options[$name])) {
        // is not auto loaded
        $CI->db->select('option_val');
        $CI->db->where('option_name', $name);
        $row = $CI->db->get(db_prefix() . 'timesheets_option')->row();
        if ($row) {
            $val = $row->option_val;
        }
    } else {
        $val = $options[$name];
    }

    return hooks()->apply_filters('get_timesheets_option', $val, $name);
}

/**
 * row timesheets options exist
 * @param  string $name
 * @return 
 */
function row_timesheets_options_exist($name){
    $CI = & get_instance();
    $i = count($CI->db->query('Select * from '.db_prefix().'timesheets_option where option_name = '.$name)->result_array());
    if($i == 0){
        return 0;
    }
    if($i > 0){
        return 1;
    }
}

/**
 * handle timesheets attachments array
 * @param  int $staffid
 * @param  string $index_name
 * @return 
 */
function handle_timesheets_attachments_array($staffid, $index_name = 'attachments')
{
    $uploaded_files = [];
    $path           = TIMESHEETS_MODULE_UPLOAD_FOLDER.'/'.$staffid .'/';
    $CI             = &get_instance();
    if (isset($_FILES[$index_name]['name'])
        && ($_FILES[$index_name]['name'] != '' || is_array($_FILES[$index_name]['name']) && count($_FILES[$index_name]['name']) > 0)) {
        if (!is_array($_FILES[$index_name]['name'])) {
            $_FILES[$index_name]['name']     = [$_FILES[$index_name]['name']];
            $_FILES[$index_name]['type']     = [$_FILES[$index_name]['type']];
            $_FILES[$index_name]['tmp_name'] = [$_FILES[$index_name]['tmp_name']];
            $_FILES[$index_name]['error']    = [$_FILES[$index_name]['error']];
            $_FILES[$index_name]['size']     = [$_FILES[$index_name]['size']];
        }

        _file_attachments_index_fix($index_name);
        for ($i = 0; $i < count($_FILES[$index_name]['name']); $i++) {
            // Get the temp file path
            $tmpFilePath = $_FILES[$index_name]['tmp_name'][$i];

            // Make sure we have a filepath
            if (!empty($tmpFilePath) && $tmpFilePath != '') {
                if (_perfex_upload_error($_FILES[$index_name]['error'][$i])
                    || !_upload_extension_allowed($_FILES[$index_name]['name'][$i])) {
                    continue;
                }

                _maybe_create_upload_path($path);
                $filename    = unique_filename($path, $_FILES[$index_name]['name'][$i]);
                $newFilePath = $path . $filename;

                // Upload the file into the temp dir
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    array_push($uploaded_files, [
                    'file_name' => $filename,
                    'filetype'  => $_FILES[$index_name]['type'][$i],
                    ]);
                    if (is_image($newFilePath)) {
                        create_img_thumb($path, $filename);
                    }
                }
            }
        }
    }

    if (count($uploaded_files) > 0) {
        return $uploaded_files;
    }

    return false;
}

/**
 * render timesheets yes/no option
 * @param  int $option_value   
 * @param  string $label          
 * @param  string $tooltip        
 * @param  string $replace_yes_text
 * @param  string $replace_no_text 
 * @param  string $replace_1       
 * @param  string $replace_0        
 * @return 
 */
function render_timesheets_yes_no_option($option_value, $label, $tooltip = '', $replace_yes_text = '', $replace_no_text = '', $replace_1 = '', $replace_0 = '')
{
    ob_start(); ?>
    <div class="form-group">
        <label for="<?php echo html_entity_decode($option_value); ?>" class="control-label clearfix">
            <?php echo($tooltip != '' ? '<i class="fa fa-question-circle" data-toggle="tooltip" data-title="' . _l($tooltip, '', false) . '"></i> ': '') . _l($label, '', false); ?>
        </label>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_1_<?php echo html_entity_decode($label); ?>" name="timesheets_setting[<?php echo html_entity_decode($option_value); ?>]" value="<?php echo html_entity_decode($replace_1) == '' ? 1 : $replace_1; ?>" <?php if (get_timesheets_option($option_value) == ($replace_1 == '' ? '1' : $replace_1)) {
        echo 'checked';
    } ?>>
            <label for="y_opt_1_<?php echo html_entity_decode($label); ?>">
                <?php echo html_entity_decode($replace_yes_text) == '' ? _l('settings_yes') : $replace_yes_text; ?>
            </label>
        </div>
        <div class="radio radio-primary radio-inline">
                <input type="radio" id="y_opt_2_<?php echo html_entity_decode($label); ?>" name="timesheets_setting[<?php echo html_entity_decode($option_value); ?>]" value="<?php echo html_entity_decode($replace_0) == '' ? 0 : $replace_0; ?>" <?php if (get_timesheets_option($option_value) == ($replace_0 == '' ? '0' : $replace_0)) {
        echo 'checked';
    } ?>>
                <label for="y_opt_2_<?php echo html_entity_decode($label); ?>">
                    <?php echo html_entity_decode($replace_no_text) == '' ? _l('settings_no') : $replace_no_text; ?>
                </label>
        </div>
    </div>
    <?php
    $settings = ob_get_contents();
    ob_end_clean();
    echo html_entity_decode($settings);
}

/**
 * timesheets reformat currency asset
 * @param  int $value
 * @return 
 */
function timesheets_reformat_currency_asset($value)
{
    return str_replace(',','', $value);
}

/**
 * get type of leave name
 * @param  int $id
 * @return 
 */
function get_type_of_leave_name($id){
    $name = '';
    switch ($id) {
        case 1:
            $name = _l('sick_leave');
            break;
        case 2:
            $name = _l('maternity_leave');
            break;
        case 3:
            $name = _l('private_work_with_pay');
            break;
        case 4:
            $name = _l('private_work_without_pay');
            break;
        case 5:
            $name = _l('child_sick');
            break;
        case 6:
            $name = _l('power_outage');
            break;
        case 7:
            $name = _l('meeting_or_studying');
            break;
        case 8:
            $name = _l('annual_leave');
            break;
    }
    return $name;
}

/**
 * handle requisition attachments
 * @param  int $id
 * @return 
 */
function handle_requisition_attachments($id)
{
    if (isset($_FILES['file']) && _perfex_upload_error($_FILES['file']['error'])) {
        header('HTTP/1.0 400 Bad error');
        echo _perfex_upload_error($_FILES['file']['error']);
        die;
    }
    $path = TIMESHEETS_MODULE_UPLOAD_FOLDER .'/requisition_leave/'  . $id . '/';
    $CI   = & get_instance();

    if (isset($_FILES['file']['name'])) {
        hooks()->do_action('before_upload_expense_attachment', $id);
        // Get the temp file path
        $tmpFilePath = $_FILES['file']['tmp_name'];
        // Make sure we have a filepath
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            _maybe_create_upload_path($path);
            $filename    = $_FILES['file']['name'];
            $newFilePath = $path . $filename;
            // Upload the file into the temp dir
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                $attachment   = [];
                $attachment[] = [
                    'file_name' => $filename,
                    'filetype'  => $_FILES['file']['type'],
                    ];

                $rs = $CI->misc_model->add_attachment_to_database($id, 'requisition', $attachment);
                return $rs;
            }
        }
    }
}