<?php

defined('BASEPATH') or exit('No direct script access allowed');

class salary extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('salary_model');
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

    /* Edit opportunity or add new opportunity*/
    public function salaries($id = '')
    {
        
        if (!has_permission('salary', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('salary', '', 'create')) {
                access_denied('salary');
            }
        }

        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                if (!has_permission('salary', '', 'create')) {
                    access_denied('salary');
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

                $staffFound = $this->salary_model->staffExist($data['staff_member']);
                if(!empty($staffFound)){
                    set_alert('success', _l('already_exist'));
                    redirect(admin_url('salary/salaries'));

                }
                $id = $this->salary_model->add($data);
                
                if (!has_permission('salary', '', 'view')) {

                }
                if ($id) {
                    set_alert('success', _l('salary_added_successfully'));
                        redirect(admin_url('salary/pdf/'.$id));

                }
            } else {
                if (!has_permission('salary', '', 'edit')) {
                    if (!is_customer_admin($id)) {
                        access_denied('salary');
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
                $success = $this->salary_model->update($data, $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully'));
                    redirect(admin_url('salary/pdf/'.$id));
                }
                redirect(admin_url('salary/'));
            }
        }
        if($id){
                $data['data'] = $this->salary_model->get($id);
        }
       $data['staff']         = $this->salary_model->getStaff('', ['active' => 1]);

        $this->load->view('salary',$data);
    }
    // creating pdf
   
    public function pdf($id)
    {

        $salary        = $this->salary_model->get($id);
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
    // fetching data from table to populate datatable
    public function table()
    {
        $this->app->get_table_data(module_views_path('salary', 'tables/salaries'));
    }
    //Editing opportunity
    public function edit($id)
    {
       $data['staff']         = $this->salary_model->getStaff('', ['active' => 1]);
       $data['data'] = $this->salary_model->get($id);
       $this->load->view('salary',$data);

    }
    public function delete($id){
        if (!has_permission('salary', '', 'delete')) {
            access_denied('salary');
        }
        $this->salary_model->delete($id);
        set_alert('success', _l('Deleted Successfully', _l('salary')));
        redirect('salary/');
    }
    //bulk action function
    public function bulk_action()
    {
        if (!has_permission('salary', '', 'delete')) {
            access_denied('salary');
        }
        //  hooks()->do_action('before_do_bulk_action_for_customers');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids    = $this->input->post('ids');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->salary_model->delete($id)) {
                            $total_deleted++;
                        }
                    }

                }
            }
        }


        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_clients_deleted', $total_deleted));
        }
    }
}