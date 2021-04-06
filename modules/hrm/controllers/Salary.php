<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Salary extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hrm_model');
    }

    public function index()
    {
        if (!has_permission('hrm', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('hrm', '', 'create')) {
                access_denied('hrm');
            }
        }
        $data = null;

        $this->load->view('manage', $data);
    }

    public function manage($id = '')
    {
 
        if (!has_permission('hrm', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('hrm', '', 'create')) {
                access_denied('hrm');
            }
        }

        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('hrm', '', 'create')) {
                    access_denied('hrm');
                }
                $data = $this->input->post(); //get post

                $basicSalary = $data['basic_salary'];
                $data['total_salary'] = $data['basic_salary']; //total salary = basic salary
                $bonus = isset($data['bonus']) ? $data['bonus'] : 0; // bonus if not set
                $onedaySalary = $data['basic_salary'] / 30; // one day salary = total salary / 30  
                
                if($data['basic_salary'] > 50000) //if salary > 50000
                {
                    $OntaxSalary = $data['basic_salary'] - 50000 ; // subtract 50000 from total
                    $taxDeducted = $OntaxSalary * 5/100; //calculate 5% from value greater than 50000
                    $data['basic_salary'] = $data['basic_salary'] - $taxDeducted; //subtract 5% from total
                    
                    // $data['basic_salary'] = $data['basic_salary'] * 5/100; 
                }
                if($data['unpaid_leaves'] > 0) //if leaves > 0
                { 
                    $leavesDeducted = $onedaySalary * $data['unpaid_leaves']; //one day multiply by unpaid leaves
                    $data['total_salary'] = $data['basic_salary'] - $leavesDeducted; 
                }
                $data['total_salary'] = $data['total_salary'] + $bonus;
                $data['basic_salary'] = $basicSalary;

                $staffFound = $this->hrm_model->staffExist($data['staff_member'],$data['salary_month']);
                // die($staffFound);
                if(!empty($staffFound)){
                    set_alert('danger', _l('already_exist'));
                    redirect(admin_url('hrm/salary/manage'.$id));

                }

                $data['created_at'] = date("Y-m-d");
                $data['current_month'] = date('m',strtotime($data['salary_month']));
                
                $id = $this->hrm_model->add($data);
                if ($id) {
                    
                    set_alert('success', _l('salary_added_successfully'));
                    redirect(admin_url('hrm/salary/pdf/'.$id));

                }
            } else {
                if (!has_permission('hrm', '', 'edit')) {
                    if (!is_customer_admin($id)) {
                        access_denied('hrm');
                    }
                }
                $data = $this->input->post();
                $basicSalary = $data['basic_salary'];
                $data['total_salary'] = $data['basic_salary']; //total salary = basic salary
                $bonus = isset($data['bonus']) ? $data['bonus'] : 0; // bonus if not set
                $onedaySalary = $data['basic_salary'] / 30; // one day salary = total salary / 30  
                
                if($data['basic_salary'] > 50000) //if salary > 50000
                {
                    $OntaxSalary = $data['basic_salary'] - 50000 ; // subtract 50000 from total
                    $taxDeducted = $OntaxSalary * 5/100; //calculate 5% from value greater than 50000
                    $data['basic_salary'] = $data['basic_salary'] - $taxDeducted; //subtract 5% from total
                    
                    // $data['basic_salary'] = $data['basic_salary'] * 5/100; 
                }
                if($data['unpaid_leaves'] > 0) //if leaves > 0
                { 
                    $leavesDeducted = $onedaySalary * $data['unpaid_leaves']; //one day multiply by unpaid leaves
                    $data['total_salary'] = $data['basic_salary'] - $leavesDeducted; 
                }
                $data['total_salary'] = $data['total_salary'] + $bonus;
                $data['basic_salary'] = $basicSalary;
                $success = $this->hrm_model->update($data, $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully'));
                    redirect(admin_url('hrm/salary/pdf/'.$id));
                }
                redirect(admin_url('hrm/salary'));
            }
        }
        if($id){
            $data['data'] = $this->hrm_model->get($id);
            $staffid = $data['data']->staff_member;
            $member = $this->staff_model->get($staffid);
            $data['salary'] = $member->salary;
            
        }
        $data['month'] = $this->hrm_model->get_month();
        $data['staff']  = $this->hrm_model->get_staff('', ['active' => 1]);
        $this->load->view('salary',$data);
    }
    // creating pdf
   
    public function pdf($id)
    {

        $salary   = $this->hrm_model->get($id);
        try {
            $pdf = salary_pdf($salary);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it('Salary'.$id)) . '.pdf', 'I');
    }

    public function table()
    {
        $this->app->get_table_data(module_views_path('hrm', 'tables/salary'));
    }


    public function delete($id){
        if (!has_permission('hrm', '', 'delete')) {
            access_denied('hrm');
        }
        $this->hrm_model->delete($id);
        set_alert('danger', _l('Deleted Successfully', _l('salary')));
        redirect('hrm/salary');
    }

    public function member($id = '')
    {
        // var_dump('exit');
        // exit();
        if (!has_permission('staff', '', 'view')) {
            access_denied('staff');
        }

        hooks()->do_action('staff_member_edit_view_profile', $id);

        $this->load->model('departments_model');
        // $this->load->model('hrm_model');
        if ($this->input->post()) {
            $data = $this->input->post();
            // Don't do XSS clean here.
            $data['email_signature'] = $this->input->post('email_signature', false);
            $data['email_signature'] = html_entity_decode($data['email_signature']);

            $data['password'] = $this->input->post('password', false);
            if ($id == '') {
                if (!has_permission('staff', '', 'create')) {
                    access_denied('staff');
                }
                $id = $this->hrm_model->add_staff($data);
                if ($id) {
                    handle_staff_profile_image_upload($id);
                    set_alert('success', _l('added_successfully', _l('staff_member')));
                    redirect(admin_url('hrm/salary/'));
                }
            } else {
                if (!$id == get_staff_user_id() && !is_admin() && !hrm_permissions('hrm', '', 'edit')) {
                    access_denied('hrm');
                }

                handle_staff_profile_image_upload($id);
                $response = $this->hrm_model->update_staff($data, $id);
                if (is_array($response)) {
                    if (isset($response['cant_remove_main_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_main_admin'));
                    } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                        set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
                    }
                } elseif ($response == true) {
                    set_alert('success', _l('updated_successfully', _l('staff_member')));
                }
                redirect(admin_url('hrm/salary/'));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('staff_member_lowercase'));
        } else {
            if(get_staff_user_id() != $id && !is_admin()){
                access_denied('staff');
            }
            $data['insurances']            = $this->hrm_model->get_insurance_form_staffid($id);
            $data['insurance_history']            = $this->hrm_model->get_insurance_history_from_staffid($id);
            $data['month'] = $this->hrm_model->get_month();

            $data['hrm_staff']   = $this->hrm_model->get_hrm_attachments($id);
            $recordsreceived = $this->hrm_model->get_records_received($id);
            $payslip = $this->hrm_model->get_paysplip_bystafff($id);
            if(isset($payslip)){
                $data['paysplip_month'] = $payslip[0];
                $data['paysplip_header'] = $payslip[1];
            }
            $data['payroll_column'] = $this->hrm_model->column_type('', 1);

            $data['records_received'] = json_decode($recordsreceived->records_received, true);
            $data['checkbox'] = [];
            if(isset( $data['records_received'])){
                foreach ($data['records_received'] as $value) {
                    $data['checkbox'][$value['datakey']] = $value['value'];
                }
            }
            $member = $this->staff_model->get($id);
            if (!$member) {
                blank_page('Staff Member Not Found', 'danger');
            }
            $data['member']            = $member;
            $title                     = $member->firstname . ' ' . $member->lastname;
            $data['staff_departments'] = $this->departments_model->get_staff_departments($member->staffid);

            $ts_filter_data = [];
            if ($this->input->get('filter')) {
                if ($this->input->get('range') != 'period') {
                    $ts_filter_data[$this->input->get('range')] = true;
                } else {
                    $ts_filter_data['period-from'] = $this->input->get('period-from');
                    $ts_filter_data['period-to']   = $this->input->get('period-to');
                }
            } else {
                $ts_filter_data['this_month'] = true;
            }

            $data['logged_time'] = $this->staff_model->get_logged_time_data($id, $ts_filter_data);
            
        }
        $this->load->model('currencies_model');
        $data['positions'] = '';
        $data['workplace'] ='';
        $data['base_currency'] = '';
        $data['roles']         = $this->roles_model->get();
        $data['user_notes']    = $this->misc_model->get_notes($id, 'staff');
        $data['departments']   = $this->departments_model->get();;
        $data['title']         = $title;

        $data['contract_type'] = '';
        $data['staff'] = $this->staff_model->get();
        $data['allowance_type'] = '';
        $data['salary_form'] = '';

        $this->load->view('members', $data);
    }

    public function staff_salary()
    {
        $id = $this->input->post('id');
        $member = $this->staff_model->get($id);
        $data = $member->salary;
        // echo "<pre>";
        // var_dump($member->salary);
        // die();
        echo json_encode($data);
    }
}