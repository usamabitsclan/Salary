<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hrm extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('salary');
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

    
}