<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Transaction extends Admin_Controller
{
    public $search_type;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('transaction_model');

        $this->load->model('printing_model');

        $this->load->model(array('transaction_model'));
        $this->load->library("datatables");
        $this->load->library('SaasValidation');
        $this->config->load("payroll");
        $this->search_type = $this->config->item('search_type');
    }

    public function printTransaction()
    {
        $print_details         = $this->printing_model->get('', 'paymentreceipt');
        $id                    = $this->input->post('id');
        $transaction           = $this->transaction_model->getTransaction($id);
        $data['transaction']   = $transaction;
        $data['print_details'] = $print_details;
        $page                  = $this->load->view('admin/transaction/_printTransaction', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function printAllTransactions()
    {
        $this->load->model('patient_model');
        $this->load->model('charge_model');
        $ipd_id          = $this->input->post('ipd_id');
        $opd_id          = $this->input->post('opd_id');
        $total_charge    = $this->input->post('total_charge');
        $print_details   = $this->printing_model->get('', 'paymentreceipt');

        if (!empty($ipd_id)) {
            $patient         = $this->patient_model->getIpdDetails($ipd_id);
            $payment_details = $this->transaction_model->IPDPatientPayments($ipd_id);
            if ($total_charge === null || $total_charge === '') {
                $charges = $this->charge_model->getCharges($ipd_id);
                $total_charge = 0;
                if (!empty($charges)) {
                    foreach ($charges as $ch) {
                        $total_charge += (float)$ch['amount'];
                    }
                }
            }
        } elseif (!empty($opd_id)) {
            $patient         = $this->patient_model->getopdDetails($opd_id);
            $payment_details = $this->transaction_model->OPDPatientPayments($opd_id);
            if ($total_charge === null || $total_charge === '') {
                $charges = $this->charge_model->getOPDCharges($opd_id);
                $total_charge = 0;
                if (!empty($charges)) {
                    foreach ($charges as $ch) {
                        $total_charge += (float)$ch['amount'];
                    }
                }
            }
        } else {
            $patient         = array();
            $payment_details = array();
            $total_charge    = 0;
        }

        $data['patient']         = $patient;
        $data['payment_details'] = $payment_details;
        $data['print_details']   = $print_details;
        $data['total_charge']    = $total_charge;
        $page                    = $this->load->view('admin/transaction/_printAllTransactions', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function deleteByID()
    {
        $id          = $this->input->post('id');

        // SaaS: release the transaction's attachment from storage quota before deletion.
        // Diagnostic INFO logs trace why quota does/doesn't change — check application/logs/.
        if (!empty($id)) {
            $saas_transaction = $this->transaction_model->getTransaction($id);
            if (empty($saas_transaction)) {
                log_message('info', 'SaaS deleteByID: transaction id=' . $id . ' not found in DB, no quota release.');
            } elseif (empty($saas_transaction->attachment)) {
                log_message('info', 'SaaS deleteByID: transaction id=' . $id . ' has NULL attachment (likely cash/non-cheque payment), no quota release.');
            } else {
                $doc_path = $saas_transaction->attachment;
                $dir      = $this->media_storage->resolveAttachmentDir($doc_path);
                $kb       = $this->media_storage->getUploadedFileSize($doc_path, $dir);
                if ($kb > 0) {
                    try {
                        $this->saasvalidation->deleteResouceQuota('storage', $kb);
                        log_message('info', 'SaaS deleteByID: released ' . $kb . ' KB for transaction id=' . $id . ' attachment=' . $doc_path);
                    } catch (Exception $e) {
                        log_message('error', 'SaaS storage quota release failed (transaction deleteByID id=' . $id . '): ' . $e->getMessage());
                    }
                } else {
                    log_message('info', 'SaaS deleteByID: transaction id=' . $id . ' attachment=' . $doc_path . ' file missing on disk (size=0), no quota release.');
                }
                $this->media_storage->filedelete($doc_path, $dir);
            }
        }

        $transaction = $this->transaction_model->delete($id);
        $array       = array('status' => 'success', 'message' => $this->lang->line('delete_message'));
        echo json_encode($array);
    }

    public function download($trans_id)
    {
        $transaction = $this->transaction_model->getTransaction($trans_id);
        $this->media_storage->filedownload($transaction->attachment,'./uploads/payment_document/');
    }

    public function download_cheque_attachment($trans_id)
    {
        $transaction = $this->transaction_model->getTransaction($trans_id);
        $this->media_storage->filedownload($transaction->attachment,'./uploads/patient_timeline/');
    }

    public function transactionreport()
    {
        if (!$this->rbac->hasPrivilege('daily_transaction_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/finance');
        $this->session->set_userdata('subsub_menu', 'reports/transaction/dailytransactionreport');

        $data['title'] = 'title';
        $this->form_validation->set_rules('date_from', $this->lang->line('date_from'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date_to', $this->lang->line('date_to'), 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'date_from' => form_error('date_from'),
                'date_to'   => form_error('date_to'),
            );
            $json_array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $date_from = $this->customlib->dateFormatToYYYYMMDD($this->input->post('date_from'));
            $date_to   = $this->customlib->dateFormatToYYYYMMDD($this->input->post('date_to'));

            $reportdata = $this->transaction_model->getTransactionBetweenDate($date_from, $date_to, 'all');
            $start_date = strtotime($date_from);
            $end_date   = strtotime($date_to);
            $date_array = array();
            for ($i = $start_date; $i <= $end_date; $i += 86400) {
                $date_array[date('Y-m-d', $i)] = array('amount' => 0, 'refund_amount' => 0, 'online_transaction' => 0, 'offline_transaction' => 0, 'total_transaction' => 0);
            }

            if (!empty($reportdata)) {
                foreach ($reportdata as $key => $value) {
                    if ($value->type == 'payment') {
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['amount']            = $date_array[date('Y-m-d', strtotime($value->payment_date))]['amount'] + $value->amount;
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['total_transaction'] = $date_array[date('Y-m-d', strtotime($value->payment_date))]['total_transaction'] + 1;

                        if ($value->payment_mode == "Online") {
                            $date_array[date('Y-m-d', strtotime($value->payment_date))]['online_transaction'] = $date_array[date('Y-m-d', strtotime($value->payment_date))]['online_transaction'] + $value->amount;
                        } else {
                            $date_array[date('Y-m-d', strtotime($value->payment_date))]['offline_transaction'] = $date_array[date('Y-m-d', strtotime($value->payment_date))]['offline_transaction'] + $value->amount;
                        }
                    } elseif ($value->type == 'refund') {
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['refund_amount'] += $value->amount;
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['amount'] -= $value->amount;
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['total_transaction'] += 1;
                    }
                }
            }

            $dt_data = array();
            foreach ($date_array as $dt_key => $dt_value) {
                $row                        = array();
                $row['date']                = $dt_key;
                $row['total_transaction']   = $dt_value['total_transaction'];
                $row['online_transaction']  = $dt_value['online_transaction'];
                $row['offline_transaction'] = $dt_value['offline_transaction'];
                $row['refund_amount']       = $dt_value['refund_amount'];
                $row['amount']              = $dt_value['amount'];
                $dt_data[]                  = $row;
            }

            $data['result'] = $dt_data;
        }

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/transaction/transactionreport', $data);
        $this->load->view('layout/footer', $data);
    } 

    public function print_dailytransaction_report()
    {
        if (!$this->rbac->hasPrivilege('daily_transaction_report', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access Denied'));
            return;
        }

        $date_from_post = $this->input->post('date_from', TRUE);
        $date_to_post   = $this->input->post('date_to', TRUE);

        if (empty($date_from_post)) {
            $date_from_post = date($this->customlib->getHospitalDateFormat());
        }
        if (empty($date_to_post)) {
            $date_to_post = date($this->customlib->getHospitalDateFormat());
        }

        $date_from = $this->customlib->dateFormatToYYYYMMDD($date_from_post);
        $date_to   = $this->customlib->dateFormatToYYYYMMDD($date_to_post);

        $reportdata = $this->transaction_model->getTransactionBetweenDate($date_from, $date_to, 'all');
        $start_date = strtotime($date_from);
        $end_date   = strtotime($date_to);
        $date_array = array();
        for ($i = $start_date; $i <= $end_date; $i += 86400) {
            $date_array[date('Y-m-d', $i)] = array('amount' => 0, 'refund_amount' => 0, 'online_transaction' => 0, 'offline_transaction' => 0, 'total_transaction' => 0);
        }

        if (!empty($reportdata)) {
            foreach ($reportdata as $key => $value) {
                if ($value->type == 'payment') {
                    $d = date('Y-m-d', strtotime($value->payment_date));
                    if (isset($date_array[$d])) {
                        $date_array[$d]['amount']            += (float)$value->amount;
                        $date_array[$d]['total_transaction'] += 1;

                        if ($value->payment_mode == "Online") {
                            $date_array[$d]['online_transaction'] += (float)$value->amount;
                        } else {
                            $date_array[$d]['offline_transaction'] += (float)$value->amount;
                        }
                    }
                } elseif ($value->type == 'refund') {
                    $d = date('Y-m-d', strtotime($value->payment_date));
                    if (isset($date_array[$d])) {
                        $date_array[$d]['refund_amount']     += (float)$value->amount;
                        $date_array[$d]['amount']            -= (float)$value->amount;
                        $date_array[$d]['total_transaction'] += 1;
                    }
                }
            }
        }

        $hospital_name = 'YOUR HOSPITAL NAME';
        if (isset($this->setting_model)) {
            $h_details = $this->setting_model->getHospitalDetails();
            if (!empty($h_details) && !empty($h_details->name)) {
                $hospital_name = $h_details->name;
            }
        }

        $report_subtitle = "Daily Transaction Report [From: " . date('d-M-Y', $start_date) . " To: " . date('d-M-Y', $end_date) . "]";

        $print_rows         = array();
        $total_transactions = 0;
        $total_online       = 0;
        $total_offline      = 0;
        $total_refund       = 0;
        $net_total_amount   = 0;

        foreach ($date_array as $dt_key => $dt_value) {
            $trans   = (int)$dt_value['total_transaction'];
            $online  = (float)$dt_value['online_transaction'];
            $offline = (float)$dt_value['offline_transaction'];
            $refund  = (float)$dt_value['refund_amount'];
            $amt     = (float)$dt_value['amount'];

            $total_transactions += $trans;
            $total_online       += $online;
            $total_offline      += $offline;
            $total_refund       += $refund;
            $net_total_amount   += $amt;

            $date_disp = date('d-M-Y', strtotime($dt_key));

            $print_rows[] = array(
                'date'                => $date_disp,
                'total_transaction'   => $trans,
                'online_transaction'  => number_format($online, 2),
                'offline_transaction' => number_format($offline, 2),
                'refund_amount'       => ($refund > 0 ? '-' : '') . number_format($refund, 2),
                'refund_val'          => $refund,
                'amount'              => number_format($amt, 2),
                'amount_val'          => $amt,
            );
        }

        $data['hospital_name']      = $hospital_name;
        $data['report_subtitle']    = $report_subtitle;
        $data['print_rows']         = $print_rows;
        $data['total_transactions'] = $total_transactions;
        $data['total_online']       = $total_online;
        $data['total_offline']      = $total_offline;
        $data['total_refund']       = $total_refund;
        $data['net_total_amount']   = $net_total_amount;
        $data['currency_symbol']    = $this->customlib->getHospitalCurrencyFormat();

        $html = $this->load->view('admin/transaction/_printDailyTransactionReport', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }


    public function processingtransactionreport()
    {
        if (!$this->rbac->hasPrivilege('processing_transaction_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/finance');
        $this->session->set_userdata('subsub_menu', 'reports/transaction/processingtransactionreport');

        $data['title'] = 'title';
        $this->form_validation->set_rules('date_from', $this->lang->line('date_from'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date_to', $this->lang->line('date_to'), 'trim|required|xss_clean');
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'date_from' => form_error('date_from'),
                'date_to'   => form_error('date_to'),
            );
            $json_array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $date_from = $this->customlib->dateFormatToYYYYMMDD($this->input->post('date_from'));
            $date_to   = $this->customlib->dateFormatToYYYYMMDD($this->input->post('date_to'));

            $reportdata = $this->transaction_model->getTransactionBetweenDate($date_from, $date_to, 'payment');
            $start_date = strtotime($date_from);
            $end_date   = strtotime($date_to);
            $date_array = array();
            for ($i = $start_date; $i <= $end_date; $i += 86400) {
                $date_array[date('Y-m-d', $i)] = array('amount' => 0, 'online_transaction' => 0, 'offline_transaction' => 0, 'total_transaction' => 0);
            }

            if (!empty($reportdata)) {
                foreach ($reportdata as $key => $value) {

                    $date_array[date('Y-m-d', strtotime($value->payment_date))]['amount']            = $date_array[date('Y-m-d', strtotime($value->payment_date))]['amount'] + $value->amount;
                    $date_array[date('Y-m-d', strtotime($value->payment_date))]['total_transaction'] = $date_array[date('Y-m-d', strtotime($value->payment_date))]['total_transaction'] + 1;

                    if ($value->payment_mode == "Online") {
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['online_transaction'] = $date_array[date('Y-m-d', strtotime($value->payment_date))]['online_transaction'] + $value->amount;
                    } else {
                        $date_array[date('Y-m-d', strtotime($value->payment_date))]['offline_transaction'] = $date_array[date('Y-m-d', strtotime($value->payment_date))]['offline_transaction'] + $value->amount;
                    }
                }
            }

            $dt_data = array();
            foreach ($date_array as $dt_key => $dt_value) {
                $row                        = array();
                $row['date']                = $dt_key;
                $row['total_transaction']   = $dt_value['total_transaction'];
                $row['online_transaction']  = $dt_value['online_transaction'];
                $row['offline_transaction'] = $dt_value['offline_transaction'];
                $row['amount']              = $dt_value['amount'];
                $dt_data[]                  = $row;
            }

            $data['result'] = $dt_data;
        }

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/transaction/processingtransactionreport', $data);
        $this->load->view('layout/footer', $data);
    } 

    public function gettransactionbydate()
    {
        if (!$this->rbac->hasPrivilege('daily_transaction_report', 'can_view')) {
            access_denied();
        }
        $date          = $this->input->post('date');
        $data['title'] = 'title';
        $result         = $this->transaction_model->getTransactionBetweenDate($date, $date, 'all');
        $data['result'] = $result;
        $page           = $this->load->view('admin/transaction/_gettransactionbydate', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function print_collection_list()
    {
        if (!$this->rbac->hasPrivilege('daily_transaction_report', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access Denied'));
            return;
        }

        $date   = $this->input->post('date');
        $result = $this->transaction_model->getTransactionBetweenDate($date, $date, 'all');

        $hospital_name = 'YOUR HOSPITAL NAME';
        if (isset($this->setting_model)) {
            $h_details = $this->setting_model->getHospitalDetails();
            if (!empty($h_details) && !empty($h_details->name)) {
                $hospital_name = $h_details->name;
            }
        }

        $date_formatted = !empty($date) ? date('d-M-Y', strtotime($date)) : '-';
        $report_subtitle = "Daily Collection List [Date: " . $date_formatted . "]";

        $print_rows   = array();
        $total_amount = 0;

        if (!empty($result)) {
            $trans_prefix = $this->customlib->getSessionPrefixByType('transaction_id');
            foreach ($result as $dt_value) {
                $amt_num = (float)$dt_value->amount;
                if ($dt_value->type == 'refund') {
                    $total_amount -= $amt_num;
                    $amt_disp = '-' . number_format($amt_num, 2);
                    $amt_val = -$amt_num;
                } else {
                    $total_amount += $amt_num;
                    $amt_disp = number_format($amt_num, 2);
                    $amt_val = $amt_num;
                }

                $staff_name = trim(($dt_value->name ?? '') . ' ' . ($dt_value->surname ?? ''));
                if (empty($staff_name)) {
                    $staff_name = '-';
                }

                $pay_mode = !empty($dt_value->payment_mode) ? $this->lang->line(strtolower($dt_value->payment_mode)) : '-';

                $print_rows[] = array(
                    'transaction_id' => $trans_prefix . $dt_value->id,
                    'date'           => !empty($dt_value->payment_date) ? date('d-M-Y', strtotime($dt_value->payment_date)) : '-',
                    'payment_mode'   => $pay_mode,
                    'collected_by'   => $staff_name,
                    'amount'         => $amt_disp,
                    'amount_val'     => $amt_val,
                );
            }
        }

        $data['hospital_name']   = $hospital_name;
        $data['report_subtitle'] = $report_subtitle;
        $data['print_rows']      = $print_rows;
        $data['total_amount']    = $total_amount;
        $data['currency_symbol'] = $this->customlib->getHospitalCurrencyFormat();

        $html = $this->load->view('admin/transaction/_printCollectionList', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }

    public function processingtransaction(){
        $dt_response = $this->transaction_model->getAllprocessingtransactionRecord();
        
        $dt_response = json_decode($dt_response);

        $dt_data = array();
        if (!empty($dt_response->data)) {
            foreach ($dt_response->data as $key => $value) {

                $row = array();
                $row[] = $value->patient_name .' ('. $value->patients_id .')';
                $row[] = $this->customlib->YYYYMMDDTodateFormat($value->payment_date);
                $row[] = $value->case_reference_id;
                if(!empty($value->opd_id)){
                $row[]=$this->customlib->getSessionPrefixByType('opd_no').$value->opd_id;
                }else{
                $row[]='';
                }
                if(!empty($value->ipd_id)){
                $row[]=$this->customlib->getSessionPrefixByType('opd_no').$value->ipd_id;
                }else{
                $row[]='';
                }
                if(!empty($value->pharmacy_bill_basic_id)){
                $row[]=$this->customlib->getSessionPrefixByType('pharmacy_billing').$value->pharmacy_bill_basic_id;
                }else{
                $row[]='';
                }
                if(!empty($value->pathology_billing_id)){
                $row[]=$this->customlib->getSessionPrefixByType('pathology_billing').$value->pathology_billing_id;
                }else{
                $row[]='';
                }
                if(!empty($value->radiology_billing_id)){
                $row[]=$this->customlib->getSessionPrefixByType('radiology_billing').$value->radiology_billing_id;
                }else{
                $row[]='';
                }
                if(!empty($value->blood_donor_cycle_id)){
                $row[]=$this->customlib->getSessionPrefixByType('blood_bank_billing').$value->blood_donor_cycle_id;
                }else{
                $row[]='';
                }
                if(!empty($value->blood_issue_id)){
                $row[]=$this->customlib->getSessionPrefixByType('blood_bank_billing').$value->blood_issue_id;
                }else{
                $row[]='';
                }
                if(!empty($value->ambulance_call_id)){
                $row[]=$this->customlib->getSessionPrefixByType('ambulance_call_billing').$value->ambulance_call_id;
                }else{
                $row[]='';
                }
                if(!empty($value->appointment_id)){
                $row[]=$this->customlib->getSessionPrefixByType('appointment').$value->appointment_id;
                }else{
                $row[]='';
                }

                 $row[] = $value->amount;
                $row[] = $this->lang->line(strtolower($value->payment_mode));
               $row[] = $value->note;

                $dt_data[] = $row;
            }
        }
        $json_data = array(
            "draw"            => intval($dt_response->draw),
            "recordsTotal"    => intval($dt_response->recordsTotal),
            "recordsFiltered" => intval($dt_response->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function print_processingtransaction_report()
    {
        if (!$this->rbac->hasPrivilege('processing_transaction_report', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access Denied'));
            return;
        }

        $dt_response = $this->transaction_model->getAllprocessingtransactionRecord();
        $dt_response = json_decode($dt_response);

        $hospital_name = 'YOUR HOSPITAL NAME';
        if (isset($this->setting_model)) {
            $h_details = $this->setting_model->getHospitalDetails();
            if (!empty($h_details) && !empty($h_details->name)) {
                $hospital_name = $h_details->name;
            }
        }

        $report_subtitle = "Processing Transaction Report";

        $print_rows   = array();
        $total_amount = 0;

        if (!empty($dt_response->data)) {
            foreach ($dt_response->data as $value) {
                $amt = (float)$value->amount;
                $total_amount += $amt;

                $clean_patient = preg_replace('/\s*\([^)]*\)$/', '', $value->patient_name ?? '');

                $ref_no = '';
                if (!empty($value->opd_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('opd_no') . $value->opd_id;
                } elseif (!empty($value->ipd_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('opd_no') . $value->ipd_id;
                } elseif (!empty($value->pharmacy_bill_basic_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('pharmacy_billing') . $value->pharmacy_bill_basic_id;
                } elseif (!empty($value->pathology_billing_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('pathology_billing') . $value->pathology_billing_id;
                } elseif (!empty($value->radiology_billing_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('radiology_billing') . $value->radiology_billing_id;
                } elseif (!empty($value->blood_donor_cycle_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('blood_bank_billing') . $value->blood_donor_cycle_id;
                } elseif (!empty($value->blood_issue_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('blood_bank_billing') . $value->blood_issue_id;
                } elseif (!empty($value->ambulance_call_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('ambulance_call_billing') . $value->ambulance_call_id;
                } elseif (!empty($value->appointment_id)) {
                    $ref_no = $this->customlib->getSessionPrefixByType('appointment') . $value->appointment_id;
                }

                $pay_mode = !empty($value->payment_mode) ? $this->lang->line(strtolower($value->payment_mode)) : '-';

                $print_rows[] = array(
                    'patient_name' => $clean_patient,
                    'date'         => !empty($value->payment_date) ? date('d-M-Y', strtotime($value->payment_date)) : '-',
                    'case_ref'     => !empty($value->case_reference_id) ? $value->case_reference_id : '-',
                    'reference_no' => !empty($ref_no) ? $ref_no : '-',
                    'amount'       => number_format($amt, 2),
                    'payment_mode' => $pay_mode,
                    'note'         => !empty($value->note) ? $value->note : '-',
                );
            }
        }

        $data['hospital_name']   = $hospital_name;
        $data['report_subtitle'] = $report_subtitle;
        $data['print_rows']      = $print_rows;
        $data['total_amount']    = $total_amount;
        $data['currency_symbol'] = $this->customlib->getHospitalCurrencyFormat();

        $html = $this->load->view('admin/transaction/_printProcessingTransactionReport', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }

    public function departmentwisetransactionreport()
    {
        if (!$this->rbac->hasPrivilege('department_wise_transaction_report', 'can_view') && !$this->rbac->hasPrivilege('daily_transaction_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/finance');
        $this->session->set_userdata('subsub_menu', 'reports/transaction/departmentwisetransactionreport');

        $data['title']       = $this->lang->line('department_wise_transaction_report');
        $data["searchlist"]  = $this->search_type;
        $data['departments'] = array(
            'all'         => $this->lang->line('all'),
            'appointment' => $this->lang->line('appointment'),
            'opd'         => $this->lang->line('opd'),
            'ipd'         => $this->lang->line('ipd'),
            'pharmacy'    => $this->lang->line('pharmacy_bill'),
            'pathology'   => $this->lang->line('pathology_test'),
            'radiology'   => $this->lang->line('radiology_test'),
            'blood_bank'  => $this->lang->line('blood_bank'),
            'ambulance'   => $this->lang->line('ambulance_call'),
        );
        $data['module']      = 'finance';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/transaction/departmentwisetransactionreport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function checkvalidationdepartment()
    {
        $search = $this->input->post('search');
        $this->form_validation->set_rules('search_type', $this->lang->line('search_type'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                'search_type' => form_error('search_type'),
            );
            $json_array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $param = array(
                'search_type' => $this->input->post('search_type', TRUE),
                'department'  => $this->input->post('department', TRUE),
                'date_from'   => $this->input->post('date_from', TRUE),
                'date_to'     => $this->input->post('date_to', TRUE),
            );

            $json_array = array('status' => 'success', 'error' => '', 'param' => $param, 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($json_array);
    }

    public function dtdepartmentwisetransactionreport()
    {
        $search_type = $this->input->post('search_type', TRUE);
        $department  = $this->input->post('department', TRUE) ?: 'all';
        $date_from   = $this->input->post('date_from', TRUE);
        $date_to     = $this->input->post('date_to', TRUE);
        $start_date  = '';
        $end_date    = '';
        $currency_symbol = $this->customlib->getHospitalCurrencyFormat();

        if ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($date_from);
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($date_to);
        } else {
            if (!empty($search_type)) {
                $dates = $this->customlib->get_betweendate($search_type);
            } else {
                $dates = $this->customlib->get_betweendate('this_year');
            }
            $start_date = $dates['from_date'];
            $end_date   = $dates['to_date'];
        }

        $reportdata = $this->transaction_model->departmentWiseTransactionRecord($start_date, $end_date, $department);
        $reportdata = json_decode($reportdata);
        $dt_data    = array();
        $total_amount = 0;

        if (!empty($reportdata->data)) {
            $trans_prefix = $this->customlib->getSessionPrefixByType('transaction_id');
            foreach ($reportdata->data as $value) {
                $amt = (float)$value->amount;
                if ($value->type == 'refund') {
                    $total_amount -= $amt;
                    $amt_display = '<span class="text-danger">-' . number_format($amt, 2) . '</span>';
                } else {
                    $total_amount += $amt;
                    $amt_display = number_format($amt, 2);
                }

                $clean_patient = preg_replace('/\s*\([^)]*\)$/', '', $value->patient_name ?? '');

                $ref_prefix = '';
                if (!empty($value->ward)) {
                    $ref_prefix = $this->customlib->getSessionPrefixByType($value->ward);
                }
                $ref_display = !empty($value->reference) ? $ref_prefix . $value->reference : '-';

                $row   = array();
                $row[] = !empty($value->payment_date) ? '<span style="white-space: nowrap;">' . $this->customlib->YYYYMMDDTodateFormat($value->payment_date) . '</span>' : '-';
                $row[] = '<span style="white-space: nowrap;">' . $trans_prefix . $value->id . '</span>';
                $row[] = !empty($value->department) ? $value->department : '-';
                $row[] = !empty($clean_patient) ? $clean_patient : '-';
                $row[] = '<span style="white-space: nowrap;">' . $ref_display . '</span>';
                $row[] = !empty($value->payment_mode) ? $this->lang->line(strtolower($value->payment_mode)) : '-';
                $row[] = '<span style="white-space: nowrap;">' . $amt_display . '</span>';

                $dt_data[] = $row;
            }

            $footer_row   = array();
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "";
            $footer_row[] = "<b>" . $this->lang->line('total_amount') . "</b>:";
            $footer_row[] = "<b>" . $currency_symbol . number_format($total_amount, 2) . "</b>";
            $dt_data[]    = $footer_row;
        }

        $json_data = array(
            "draw"            => intval($reportdata->draw ?? 1),
            "recordsTotal"    => intval($reportdata->recordsTotal ?? 0),
            "recordsFiltered" => intval($reportdata->recordsFiltered ?? 0),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function print_departmentwisetransaction_report()
    {
        if (!$this->rbac->hasPrivilege('department_wise_transaction_report', 'can_view') && !$this->rbac->hasPrivilege('daily_transaction_report', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access Denied'));
            return;
        }

        $search_type = $this->input->post('search_type', TRUE);
        $department  = $this->input->post('department', TRUE) ?: 'all';
        $date_from   = $this->input->post('date_from', TRUE);
        $date_to     = $this->input->post('date_to', TRUE);
        $start_date  = '';
        $end_date    = '';

        if ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($date_from);
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($date_to);
        } else {
            if (!empty($search_type)) {
                $dates = $this->customlib->get_betweendate($search_type);
            } else {
                $dates = $this->customlib->get_betweendate('this_year');
            }
            $start_date = $dates['from_date'];
            $end_date   = $dates['to_date'];
        }

        $records = $this->transaction_model->getDepartmentWiseTransactionList($start_date, $end_date, $department);

        $hospital_name = 'YOUR HOSPITAL NAME';
        if (isset($this->setting_model)) {
            $h_details = $this->setting_model->getHospitalDetails();
            if (!empty($h_details) && !empty($h_details->name)) {
                $hospital_name = $h_details->name;
            }
        }

        $dept_label = !empty($department) && $department != 'all' ? ucfirst(str_replace('_', ' ', $department)) : 'All Departments';
        if ($search_type == 'period' && !empty($start_date) && !empty($end_date)) {
            $duration_label = "From: " . date('d-M-Y', strtotime($start_date)) . " To: " . date('d-M-Y', strtotime($end_date));
        } else {
            $duration_label = !empty($search_type) ? (isset($this->search_type[$search_type]) ? $this->search_type[$search_type] : ucfirst(str_replace('_', ' ', $search_type))) : 'This Year';
        }

        $report_subtitle = "Department Wise Transaction Report [Department: " . $dept_label . " | Duration: " . $duration_label . "]";

        $print_rows   = array();
        $total_amount = 0;
        $total_refund = 0;

        if (!empty($records)) {
            $trans_prefix = $this->customlib->getSessionPrefixByType('transaction_id');
            foreach ($records as $value) {
                $amt = (float)$value->amount;
                $is_refund = ($value->type == 'refund');

                if ($is_refund) {
                    $total_refund += $amt;
                    $amt_display = '-' . number_format($amt, 2);
                } else {
                    $total_amount += $amt;
                    $amt_display = number_format($amt, 2);
                }

                $clean_patient = preg_replace('/\s*\([^)]*\)$/', '', $value->patient_name ?? '');

                $ref_prefix = '';
                if (!empty($value->ward)) {
                    $ref_prefix = $this->customlib->getSessionPrefixByType($value->ward);
                }
                $ref_display = !empty($value->reference) ? $ref_prefix . $value->reference : '-';

                $print_rows[] = array(
                    'date'           => !empty($value->payment_date) ? date('d-M-Y', strtotime($value->payment_date)) : '-',
                    'transaction_id' => $trans_prefix . $value->id,
                    'department'     => !empty($value->department) ? $value->department : '-',
                    'patient_name'   => !empty($clean_patient) ? $clean_patient : '-',
                    'reference_no'   => $ref_display,
                    'payment_mode'   => !empty($value->payment_mode) ? $this->lang->line(strtolower($value->payment_mode)) : '-',
                    'amount'         => $amt_display,
                    'is_refund'      => $is_refund,
                );
            }
        }

        $data['hospital_name']   = $hospital_name;
        $data['report_subtitle'] = $report_subtitle;
        $data['print_rows']      = $print_rows;
        $data['total_amount']    = $total_amount;
        $data['total_refund']    = $total_refund;
        $data['net_amount']      = $total_amount - $total_refund;
        $data['currency_symbol'] = $this->customlib->getHospitalCurrencyFormat();

        $html = $this->load->view('admin/transaction/_printDepartmentWiseTransactionReport', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }
}
