<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hrm extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('hrm');
    }
    /* List all opportunities */
    public function index()
    {
        if (!has_permission('salary', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('salary', '', 'create')) {
                access_denied('salary');
            }
        }
        $data = null;

        $this->load->view('manage', $data);
    }

    public function staff_infor()
    {
        $this->load->model('departments_model');
        $this->load->model('roles_model');
        if (!has_permission('hrm', '', 'view')) {
            access_denied('hrm');
        }

         if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('hrm', 'table_staff'));
        }
        $data['staff_members'] = $this->hrm_model->get_staff('', ['active' => 1]);
        $data['title']                 = _l('staff_infor');

        $data['dep_tree'] = json_encode($this->hrm_model->get_department_tree());
        $data['staff_role'] = $this->roles_model->get();
        
        $this->load->view('manage_staff', $data);
    }
    public function hr_code_exists()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                // First we need to check if the email is the same
                $memberid = $this->input->post('memberid');
                if ($memberid != '') {
                    $this->db->where('staffid', $memberid);
                    $staff = $this->db->get('tblstaff')->row();
                    if ($staff->staff_identifi == $this->input->post('staff_identifi')) {
                        echo json_encode(true);
                        die();
                    }
                }
                $this->db->where('staff_identifi', $this->input->post('staff_identifi'));
                $total_rows = $this->db->count_all_results('tblstaff');
                if ($total_rows > 0) {
                    echo json_encode(false);
                } else {
                    echo json_encode(true);
                }
                die();
            }
        }
    }

    
}