<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Referral extends Admin_Controller
{
	public $time_format;
	public $search_type;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('report_model');

        $this->load->library('form_validation');
        $this->load->model('referral_category_model');
        $this->load->model('referral_person_model');
        $this->load->model('referral_commission_model');
        $this->load->model('referral_payment_model');
        $this->load->model('patient_model');
        $this->load->helper('customfield_helper');
        $this->load->library('datatables');
        $this->load->helper('custom');
        $this->time_format = $this->customlib->getHospitalTimeFormat();
        $this->config->load("payroll");
        $this->search_type = $this->config->item('search_type');
    }

    public function category()
    {
        if (!$this->rbac->hasPrivilege('referral_category', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'setup');
        $this->session->set_userdata('sub_sidebar_menu', 'admin/referral/category');
        $this->session->set_userdata('sub_menu', 'admin/referral/commission');
        $data['category'] = $this->referral_category_model->get_category();
        $data['module'] = 'referral_payment';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/referral/category', $data);
        $this->load->view('layout/footer', $data);
    }

    public function person()
    {
        if (!$this->rbac->hasPrivilege('referral_person', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'referral_payment');
        // Top-level page: clear any stale Setup>Referral sub_menu so it doesn't stay highlighted
        $this->session->set_userdata('sub_menu', '');
        $this->session->set_userdata('sub_sidebar_menu', '');
        $data['category'] = $this->referral_category_model->get_category();
        $data['person']   = $this->referral_person_model->get_person();
        $data['type']     = $this->referral_category_model->get_type();
        $data['module'] = 'referral_payment';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/referral/person', $data);
        $this->load->view('layout/footer', $data);
    }

    public function commission()
    {        
        $this->session->set_userdata('top_menu', 'setup');
        $this->session->set_userdata('sub_sidebar_menu', 'admin/referral/commission');
        $this->session->set_userdata('sub_menu', 'admin/referral/commission');
        $data["commission"] = $this->referral_commission_model->get_commission();
        $data['category']   = $this->referral_category_model->get_category();
        $data['type']       = $this->referral_category_model->get_type();
        $data['module'] = 'referral_payment';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/referral/commission', $data);
        $this->load->view('layout/footer', $data);
    }

    public function payment()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'referral_payment');
        // Top-level page: clear any stale Setup>Referral sub_menu so it doesn't stay highlighted
        $this->session->set_userdata('sub_menu', '');
        $this->session->set_userdata('sub_sidebar_menu', '');

        $search_type = $this->input->post('search_type', TRUE);
        $date_from   = $this->input->post('date_from', TRUE);
        $date_to     = $this->input->post('date_to', TRUE);

        $start_date  = '';
        $end_date    = '';

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            if ($search_type == 'period') {
                $start_date = $this->customlib->dateFormatToYYYYMMDD($date_from);
                $end_date   = $this->customlib->dateFormatToYYYYMMDD($date_to);
            } else {
                if (isset($search_type) && $search_type != '') {
                    $dates      = $this->customlib->get_betweendate($search_type);
                    $start_date = $dates['from_date'];
                    $end_date   = $dates['to_date'];
                }
            }
        }

        $data["searchlist"]  = $this->search_type;
        $data['search_type'] = $search_type;
        $data['date_from']   = $date_from;
        $data['date_to']     = $date_to;
        $data["patients"]    = $this->patient_model->getPatientListall();
        $data['type']        = $this->referral_category_model->get_type();
        $data['person']      = $this->referral_person_model->get_person();
        $data['payment']     = $this->referral_payment_model->get_payment($start_date, $end_date);
        $data['settings']    = $this->referral_payment_model->get_referral_settings();
        $data['unpaid_list'] = $this->referral_payment_model->get_unpaid_referrals();
        $data['module']      = 'referral_payment';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/referral/payment', $data);
        $this->load->view('layout/footer', $data);
    }

    public function report()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/finance');
        $this->session->set_userdata('subsub_menu', 'reports/referral/report');

        $data["searchlist"]  = $this->search_type;
        $data['search_data'] = '';
        $data["patients"]    = $this->patient_model->getPatientListall();
        $data['type']        = $this->referral_category_model->get_type();
        $data['person']      = $this->referral_person_model->get_person();
        $data['module']      = 'referral_payment';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/referral/report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function checkvalidation()
    {
        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                'search_type' => form_error('search_type'),
            );
            $json_array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $param = array(
                'search_type'   => $this->input->post('search_type', TRUE),
                'payee'         => $this->input->post('payee', TRUE),
                'patient_type'  => $this->input->post('patient_type', TRUE),
                'patient'       => $this->input->post('patient', TRUE),
                'status'        => $this->input->post('status', TRUE),
                'date_from'     => $this->input->post('date_from', TRUE),
                'date_to'       => $this->input->post('date_to', TRUE),
            );

            $json_array = array('status' => 'success', 'error' => '', 'param' => $param, 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($json_array);
    }

    public function referral_report()
    {
        $search_type  = $this->input->post('search_type', TRUE);
        $payee        = $this->input->post('payee', TRUE);
        $patient_type = $this->input->post('patient_type', TRUE);
        $patient      = $this->input->post('patient', TRUE);
        $status       = $this->input->post('status', TRUE);
        $date_from    = $this->input->post('date_from', TRUE);
        $date_to      = $this->input->post('date_to', TRUE);

        $start_date = '';
        $end_date   = '';

        if ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($date_from);
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($date_to);
        } else {
            if (isset($search_type) && $search_type != '') {
                $dates      = $this->customlib->get_betweendate($search_type);
                $start_date = $dates['from_date'];
                $end_date   = $dates['to_date'];
            }
        }

        $reportdata      = $this->report_model->referralRecord($payee, $patient_type, $patient, $start_date, $end_date, $status);
        $currency_symbol = $this->customlib->getHospitalCurrencyFormat();
        
        $reportdata   = json_decode($reportdata);
        $dt_data      = array();
        $total_bill   = 0;
        $total_amount = 0;
        if (!empty($reportdata->data)) {
            foreach ($reportdata->data as $key => $value) {

                $total_bill += $value->bill_amount;
                $total_amount += $value->amount;

                $is_paid = (isset($value->status) && strtolower($value->status) === 'paid');
                if ($is_paid) {
                    $status_badge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa fa-check-circle me-1"></i> Paid</span>';
                } else {
                    $status_badge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fa fa-clock-o me-1"></i> Unpaid</span>';
                }

                $row       = array();
                $row[]     = $value->name;
                $row[]     = composePatientName($value->patient_name, $value->patient_id);
                $row[]     = $this->customlib->YYYYMMDDHisTodateFormat($value->date, $this->customlib->getHospitalTimeFormat());  
                $row[]     = $value->prefix . $value->billing_id;
                $row[]     = $status_badge;
                $row[]     = $value->percentage;
                $row[]     = $value->bill_amount;
                $row[]     = $value->amount;
                $dt_data[] = $row;
            }

            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('total_amount') . "</b>" . ':';
            $footer_row[] = "<b>" . $currency_symbol . (number_format($total_bill, 2, '.', '')) . "<br/>";
            
            $footer_row[] = "<b>" . $currency_symbol . (number_format($total_amount, 2, '.', '')) . "<br/>";

            $dt_data[] = $footer_row;
        }

        $json_data = array(
            "draw"            => intval($reportdata->draw),
            "recordsTotal"    => intval($reportdata->recordsTotal),
            "recordsFiltered" => intval($reportdata->recordsFiltered),
            "data"            => $dt_data,
        );

        echo json_encode($json_data);
    }

    public function print_report()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_view')) {
            access_denied();
        }

        $search_type  = $this->input->post('search_type', TRUE);
        $payee        = $this->input->post('payee', TRUE);
        $patient_type = $this->input->post('patient_type', TRUE);
        $patient      = $this->input->post('patient', TRUE);
        $status       = $this->input->post('status', TRUE);
        $date_from    = $this->input->post('date_from', TRUE);
        $date_to      = $this->input->post('date_to', TRUE);

        $start_date = '';
        $end_date   = '';

        if ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($date_from);
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($date_to);
        } else {
            if (isset($search_type) && $search_type != '') {
                $dates      = $this->customlib->get_betweendate($search_type);
                $start_date = $dates['from_date'];
                $end_date   = $dates['to_date'];
            }
        }

        $raw_records     = $this->report_model->getReferralReportData($payee, $patient_type, $patient, $start_date, $end_date, $status);
        $setting         = $this->setting_model->get();
        $hospital_name   = (!empty($setting) && isset($setting[0]['name'])) ? $setting[0]['name'] : $this->customlib->getAppName();
        $currency_symbol = $this->customlib->getHospitalCurrencyFormat();

        $period_label = '';
        if (!empty($start_date) && !empty($end_date)) {
            $from_ts = strtotime($start_date);
            $to_ts   = strtotime($end_date);
            if (date('m-Y', $from_ts) === date('m-Y', $to_ts)) {
                $period_label = "For the Month of " . date('F Y', $from_ts);
            } else {
                $period_label = "For the Period " . date('d-M-Y', $from_ts) . " to " . date('d-M-Y', $to_ts);
            }
        } elseif (!empty($start_date)) {
            $period_label = "For the Month of " . date('F Y', strtotime($start_date));
        } else {
            $period_label = "For the Month of " . date('F Y');
        }

        $grouped_doctors = array();
        foreach ($raw_records as $rec) {
            $doc_id = $rec['person_id'];
            if (!isset($grouped_doctors[$doc_id])) {
                $contact = !empty($rec['doctor_phone']) ? $rec['doctor_phone'] : $rec['doctor_contact'];
                $grouped_doctors[$doc_id] = array(
                    'doctor_name'     => $rec['doctor_name'],
                    'doctor_address'  => $rec['doctor_address'],
                    'doctor_contact'  => $contact,
                    'total_amount'    => 0,
                    'total_commission'=> 0,
                    'bills'           => array(),
                );
            }

            $bill_amt = (float)$rec['bill_amount'];
            $comm_amt = (float)$rec['amount'];

            $grouped_doctors[$doc_id]['total_amount']     += $bill_amt;
            $grouped_doctors[$doc_id]['total_commission'] += $comm_amt;

            $dept = strtoupper($rec['type'] ?: '');
            if (strpos($dept, 'PATHOLOGY') !== false) {
                $dept = 'INSIDE PATHOLOGY';
            } elseif (strpos($dept, 'RADIOLOGY') !== false) {
                $dept = 'RADIOLOGY';
            } elseif (empty($dept)) {
                $dept = 'GENERAL';
            }

            $bill_date_formatted = !empty($rec['date']) ? date('d-M-y', strtotime($rec['date'])) : '';

            $bill_number = !empty($rec['prefix']) ? ($rec['prefix'] . $rec['billing_id']) : $rec['billing_id'];
            if (empty($bill_number)) {
                $bill_number = $rec['id'];
            }

            $grouped_doctors[$doc_id]['bills'][] = array(
                'bill_no'      => $bill_number,
                'bill_date'    => $bill_date_formatted,
                'patient_name' => $rec['patient_name'],
                'department'   => $dept,
                'bill_amount'  => $bill_amt,
                'commission'   => $comm_amt,
            );
        }

        $data['grouped_doctors'] = $grouped_doctors;
        $data['hospital_name']   = $hospital_name;
        $data['period_label']    = $period_label;
        $data['currency_symbol'] = $currency_symbol;

        $html = $this->load->view('admin/referral/_printReport', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }

}
