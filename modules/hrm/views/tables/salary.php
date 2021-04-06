<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('hrm', '', 'delete');

$custom_fields = get_table_custom_fields('hrm');
$this->ci->db->query("SET sql_mode = ''");
// columns to pass datatable
$aColumns = [
    'id',
    'CONCAT('.db_prefix() .'staff.firstname, \' \', '.db_prefix() .'staff.lastname) as staff_member',
    'basic_salary',
    'paid_leaves',
    'unpaid_leaves',
    'bonus',
    'total_salary',
    'salary_month',
    // 'created_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'salaries';
$where        = [];

// Add blank where all filter can be stored
$filter = [];

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'salaries.staff_member',

];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';


    $salary_name  = $aRow['staff_member'];
    $isPerson = false;

    $url = admin_url('hrm/salary/manage/' . $aRow['id']);

    $salary_name = '<a href="' . $url . '">' . $salary_name . '</a>';

    $salary_name .= '<div class="row-options">';
    $salary_name .= '<a href="' . admin_url('hrm/salary/pdf/'. $aRow['id']).'">' . _l('view') . '</a>';

    if (!$isPerson) {
        $salary_name .= ' | <a href="' . admin_url('hrm/salary/manage/' . $aRow['id']). '">' . _l('edit') . '</a>';
    }
    if ($hasPermissionDelete) {
        $salary_name .= ' | <a href="' . admin_url('hrm/salary/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $salary_name .= '</div>';

    $row[] = $salary_name;

    $row[] = $aRow['basic_salary'];
    $row[] = $aRow['paid_leaves'];
    $row[] = $aRow['unpaid_leaves'];
    $row[] = $aRow['bonus'];
    $row[] = $aRow['total_salary'];
    $row[] = $aRow['salary_month'];
    // $row[] = $aRow['created_at'];

    $output['aaData'][] = $row;
}
