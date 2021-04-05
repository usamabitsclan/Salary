<?php

defined('BASEPATH') or exit('No direct script access allowed');


include_once(LIBSPATH.'pdf/App_pdf.php');

class salary_pdf extends App_pdf
{
    protected $salary;

    private $invoice_number;

    public function __construct($salary, $tag = '')
    {

        $GLOBALS['salary_pdf'] = $salary;
        parent::__construct();

        if (!class_exists('hrm_model', false)) {
            $this->ci->load->model('hrm_model');
        }

        $this->salary        = $salary;
    }

    public function prepare()
    {

        $this->set_view_vars([
            'salary'        => $this->salary,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'salary';
    }

    protected function file_path()
    {
        $customPath = module_dir_path('hrm').'views/'.'salary_pdf.php';
        $actualPath = module_dir_path('hrm').'views/'.'salary_pdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
