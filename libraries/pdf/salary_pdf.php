<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/App_pdf.php');

class salary_pdf extends App_pdf
{
    protected $salary;

    private $invoice_number;

    public function __construct($salary, $tag = '')
    {

        // $invoice                = hooks()->apply_filters('invoice_html_pdf_data', $invoice);
        $GLOBALS['salary_pdf'] = $salary;

        parent::__construct();

        if (!class_exists('salary_model', false)) {
            $this->ci->load->model('salary_model');
        }

        // $this->tag            = $tag;
        $this->salary        = $salary;
        // $this->invoice_number = format_invoice_number($this->invoice->id);
        // $this->load_language($this->invoice->clientid);
        // $this->SetTitle($this->invoice_number);
    }

    public function prepare()
    {
        // $this->with_number_to_word($this->invoice->clientid);

        $this->set_view_vars([
            // 'status'         => $this->invoice->status,
            // 'invoice_number' => $this->invoice_number,
            // 'payment_modes'  => $this->get_payment_modes(),
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
        
        // exit();
        $customPath = module_dir_path('salary').'views/'.'salary_pdf.php';
        $actualPath = module_dir_path('salary').'views/'.'salary_pdf.php';

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
