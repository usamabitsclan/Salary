<?php

defined('BASEPATH') or exit('No direct script access allowed');
//adding permissions
$hasPermissionDelete = has_permission('opportunity', '', 'delete');

$custom_fields = get_table_custom_fields('opportunities');
$this->ci->db->query("SET sql_mode = ''");
// columns to pass datatable
$aColumns = [
    'id',
    'project_name',
    db_prefix() . 'clients.company as account',
    'CONCAT('.db_prefix() .'staff.firstname, \' \', '.db_prefix() .'staff.lastname) as owner',
    'probability',
    'status',
    'delivery_date',
    'projected_sale_date',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'opportunity';
$where        = [];

// Add blank where all filter can be stored
$filter = [];

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'opportunity.account',
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'opportunity.owner',

];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN '.db_prefix().'customfieldsvalues as ctable_' . $key . ' ON '.db_prefix().'opportunity.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$join = hooks()->apply_filters('opportunity_table_sql_join', $join);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';
    // opportunity id
    //$row[] = $aRow['id'];

    // Company
    $company  = $aRow['project_name'];
    $isPerson = false;

    $url = admin_url('opportunity/opportunities/' . $aRow['id']);


    $company = '<a href="' . $url . '">' . $company . '</a>';

    $company .= '<div class="row-options">';
    $company .= '  <a href="' . admin_url('opportunity/opportunities/' . $aRow['id'] . '?group=profile'). '">' . _l('opportunity_view') . '</a>';

    if (!$isPerson) {
        $company .= ' | <a href="' . admin_url('opportunity/opportunities/' . $aRow['id'] . '?group=profile'). '">' . _l('opportunity_edits') . '</a>';
    }
    if ($hasPermissionDelete) {
        $company .= ' | <a href="' . admin_url('opportunity/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $company .= '</div>';

    $row[] = $company;
    //$this->load->model('opportunity_model');
    //$opportunity = $this->opportunity_model->get($id);
    //accounts
    //$id = $aRow['account'];
    //$data = $this->model->opportunity_model->get_account($id);
    $row[] = $aRow['account'];
    $row[] = $aRow['owner'];
    $row[] = $aRow['probability'];
    $row[] = $aRow['status'];
    $row[] = $aRow['delivery_date'];
    $row[] = $aRow['projected_sale_date'];
    $row[] = $aRow['created_at'];

    $row = hooks()->apply_filters('opportunity_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
