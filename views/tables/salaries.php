<?php

defined('BASEPATH') or exit('No direct script access allowed');
//adding permissions
$hasPermissionDelete = has_permission('salaries', '', 'delete');

$custom_fields = get_table_custom_fields('salaries');
$this->ci->db->query("SET sql_mode = ''");
// columns to pass datatable
$aColumns = [
    'id',
    // 'staff_member',
    // db_prefix() . 'clients.company as account',
    'CONCAT('.db_prefix() .'staff.firstname, \' \', '.db_prefix() .'staff.lastname) as staff_member',
    'basic_salary',
    'paid_leaves',
    'unpaid_leaves',
    'bonus',
    'total_salary',
    'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'salaries';
$where        = [];

// Add blank where all filter can be stored
$filter = [];

$join = [
    // 'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'opportunity.account',
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'salaries.staff_member',

];

// $join = hooks()->apply_filters('opportunity_table_sql_join', $join);

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
    $company  = $aRow['staff_member'];
    $isPerson = false;

    $url = admin_url('salary/salaries/' . $aRow['id']);


    $company = '<a href="' . $url . '">' . $company . '</a>';

    $company .= '<div class="row-options">';
    $company .= '<a href="' . admin_url('salary/pdf/'. $aRow['id']).'">' . _l('opportunity_view') . '</a>';

    if (!$isPerson) {
        $company .= ' | <a href="' . admin_url('salary/edit/' . $aRow['id']). '">' . _l('opportunity_edits') . '</a>';
    }
    if ($hasPermissionDelete) {
        $company .= ' | <a href="' . admin_url('salary/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $company .= '</div>';

    $row[] = $company;
    //$this->load->model('opportunity_model');
    //$opportunity = $this->opportunity_model->get($id);
    //accounts
    //$id = $aRow['account'];
    //$data = $this->model->opportunity_model->get_account($id);
    $row[] = $aRow['basic_salary'];
    $row[] = $aRow['paid_leaves'];
    $row[] = $aRow['unpaid_leaves'];
    $row[] = $aRow['bonus'];
    $row[] = $aRow['total_salary'];
    $row[] = $aRow['created_at'];

    // $row = hooks()->apply_filters('opportunity_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
