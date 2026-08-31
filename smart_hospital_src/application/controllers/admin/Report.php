<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Report extends Admin_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pharmacy_model');
        $this->load->model('medicine_category_model');
        $this->load->model('patient_model');

        $this->load->library('datatables');
        $this->load->model('report_model');
    }

    public function finance(){
        $data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/finance');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/finance', $data);
        $this->load->view('layout/footer', $data);
    } 

    public function appointment(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/appointment');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/appointment', $data);
        $this->load->view('layout/footer', $data);
    }

    public function opd(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/opd');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/opd', $data);
        $this->load->view('layout/footer', $data);
    }

    public function ipd(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/ipd');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/ipd', $data);
        $this->load->view('layout/footer', $data);
    }

    public function pharmacy(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/pharmacy');
        $this->session->set_userdata('subsub_menu', '');
        
        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/pharmacy', $data);
        $this->load->view('layout/footer', $data);
    }

    public function radiology(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/radiology');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/radiology', $data);
        $this->load->view('layout/footer', $data);
    }

    public function pathology(){
        $data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/pathology');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/pathology', $data);
        $this->load->view('layout/footer', $data);
    }

    public function blood_bank(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/bloodbank');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/blood_bank', $data);
        $this->load->view('layout/footer', $data);
    }
 
    public function ambulance(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/ambulance');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/ambulance', $data);
        $this->load->view('layout/footer', $data);
    }

    public function birth_death(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/birth_death');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/birth_death', $data);
        $this->load->view('layout/footer', $data);
    }
 
    public function ot(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/ot');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/ot', $data);
        $this->load->view('layout/footer', $data);
    }

    public function human_resource(){
        $data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/human_resource');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/human_resource', $data);
        $this->load->view('layout/footer', $data);
    }

    public function tpa(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/tpa');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/tpa', $data);
        $this->load->view('layout/footer', $data);
    }

    public function inventory(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/inventory');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/inventory', $data);
        $this->load->view('layout/footer', $data);
    }

    public function live_consultation(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/live_consultation');
        $this->session->set_userdata('subsub_menu', '');

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/live_consultation', $data);
        $this->load->view('layout/footer', $data);
    }

    public function log(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/log');
        $this->session->set_userdata('subsub_menu', '');
        
        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/log', $data);
        $this->load->view('layout/footer', $data);
    }

    public function patient(){
    	$data=array();
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/patient');
        $this->session->set_userdata('subsub_menu', '');
        
        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/patient', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getpharmacystock()
    {
        if (!$this->rbac->hasPrivilege('stock_report', 'can_view')) {
            access_denied();
        }
        $condition['medicine_category'] = $medicine_category =  $this->input->post('medicine_category');
        $condition['stock_type']        = $stock_type        = $this->input->post('stock_type');
        $start_date                     = '';
        $end_date                       = '';
		
        $dt_response = $this->pharmacy_model->getAllstockpharmacyRecord($condition);
        $dt_response = json_decode($dt_response);
       
        $dt_data     = array();
        if (!empty($dt_response->data)) {
            foreach ($dt_response->data as $key => $value) {
                
                $result   =   $this->pharmacy_model->getAvailableQuantity($value->id);
                
                if(!empty($result['used_quantity'])){
                    $used_quantity  =   $result['used_quantity'];
                }else{
                    $used_quantity  =  0 ;
                }                   
                $row = array();
                $available_qty = ($value->total_qty - $used_quantity);
                //====================================
                $status = "";
                $status1 = "";

                if($stock_type!=""){
                if ($available_qty <= 0 && $stock_type=="out_of_stock") {
                }elseif ( ($available_qty > 0 && $available_qty < $value->min_level) && $stock_type=="low_stock") {
                }elseif( ($available_qty <= $value->reorder_level)  && $stock_type=="reorder") {                   
                }else{
                    continue;
                }  
                }

                if ($available_qty <= 0) {
                    $status_val="out_of_stock";
                    $status = " <span class='text text-danger'> (" . $this->lang->line('out_of_stock') . ")</span>";
                } elseif ($available_qty > 0 && $available_qty < $value->min_level ) {
                    $status = " <span class='text text-warning'> (" . $this->lang->line('low_stock') . ")</span>"; 
                    $status_val="low_stock";
                }elseif($available_qty <= $value->reorder_level ) {                   
                    $status = " <span class='text text-info'> (" . $this->lang->line('reorder') . ")</span>";
                    $status_val="reorder";
                }  
                
             
                //==============================
                if(!empty($condition['stock_type'])){
                    if($status_val==$condition['stock_type']){
                        $row[]     = $value->medicine_name;
						$row[]     = $value->company_name;
						$row[]     = $value->medicine_composition;
						$row[]     = $value->medicine_category;
						$row[]     = $value->group_name;
						$row[]     = $value->unit_name;
						$row[]     = $available_qty . $status;
						$dt_data[] = $row;
                    }else{
						
                    }
                
                }else{
                    $row[]     = $value->medicine_name;
					$row[]     = $value->company_name;
					$row[]     = $value->medicine_composition;
					$row[]     = $value->medicine_category;
					$row[]     = $value->group_name;
					$row[]     = $value->unit_name;
					$row[]     = $available_qty . $status;
					$dt_data[] = $row;
                }
                
            }
        }
        $json_data = array(
            "draw"            => intval($dt_response->draw),
            "recordsTotal"    => intval(count($dt_data)),
            "recordsFiltered" => intval(count($dt_data)),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }
      public function stockcheckvalidation()
    {
        if (!$this->rbac->hasPrivilege('stock_report', 'can_view')) {
            access_denied();
        }
        $search = $this->input->post('search');
       
            $param = array(
                
                'stock_type'          => $this->input->post('stock_type'),
                'medicine_category' => $this->input->post('medicine_category'),
            );

            $json_array = array('status' => 'success', 'error' => '', 'param' => $param, 'message' => $this->lang->line('success_message'));
    
        echo json_encode($json_array);
    }

    public function stock_report()
    {
        if (!$this->rbac->hasPrivilege('stock_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/report');
        $this->session->set_userdata('subsub_menu', 'reports/report/stock_report');        
        $supplierCategory         = array('reorder'=>$this->lang->line('reorder'),'low_stock'=>$this->lang->line('low_stock'),'out_of_stock'=>$this->lang->line('out_of_stock'));
        $data["supplierCategory"] = $supplierCategory;
        $medicineCategory         = $this->medicine_category_model->getMedicineCategory();
        $data["medicineCategory"] = $medicineCategory;
        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/stock_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function balanceamountreport()
    {
        if (!$this->rbac->hasPrivilege('balance_amount_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/report');
        $this->session->set_userdata('subsub_menu', 'admin/report/balanceamountreport');
        $data['modules_type']   =  $modules_type= $this->input->post('modules_type');
        $data['patient_id']     =  $patient_id= $this->input->post('patient_id');
        $data['patient_name']   =  $patient_name= $this->input->post('patient_name');
        $data['balance_data']   = $this->report_model->getmodulewisebalance_report($modules_type,$patient_id);
        $data["modules"]        = $this->customlib->get_modules();
        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/balanceamountreport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function print_balanceamount_report()
    {
        if (!$this->rbac->hasPrivilege('balance_amount_report', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access Denied'));
            return;
        }

        $modules_type = $this->input->post('modules_type');
        $patient_id   = $this->input->post('patient_id');
        $patient_name = $this->input->post('patient_name');

        $balance_data = $this->report_model->getmodulewisebalance_report($modules_type, $patient_id);
        $modules      = $this->customlib->get_modules();

        $hospital_name = 'YOUR HOSPITAL NAME';
        if (isset($this->setting_model)) {
            $h_details = $this->setting_model->getHospitalDetails();
            if (!empty($h_details) && !empty($h_details->name)) {
                $hospital_name = $h_details->name;
            }
        }

        $module_label = 'All';
        if (!empty($modules_type)) {
            if ($modules_type == 'blood_component') {
                $module_label = 'Blood Component';
            } elseif (isset($modules[$modules_type])) {
                $module_label = $modules[$modules_type];
            } else {
                $module_label = ucfirst(str_replace('_', ' ', $modules_type));
            }
        }

        $patient_label = !empty($patient_name) ? preg_replace('/\s*\([^)]*\)$/', '', trim($patient_name)) : 'All';
        $report_subtitle = "Balance Amount Report [Head: " . $module_label . "] For Patient: " . $patient_label;

        $print_rows           = array();
        $total_amount         = 0;
        $total_discount       = 0;
        $total_tax            = 0;
        $total_net_amount     = 0;
        $total_paid_amount    = 0;
        $total_refund_amount  = 0;
        $total_balance_amount = 0;

        if (!empty($balance_data)) {
            foreach ($balance_data as $val) {
                $prefix = $this->customlib->getSessionPrefixByType($val['prefix_type']);
                $bill_no = $prefix . $val['bill_no'];
                $case_id = !empty($val['case_id']) ? $val['case_id'] : '-';

                $p_name = !empty($val['patient_name']) ? preg_replace('/\s*\([^)]*\)$/', '', trim($val['patient_name'])) : '-';

                $gen_by = trim(($val['name'] ?? '') . ' ' . ($val['surname'] ?? ''));
                if (empty($gen_by)) {
                    $gen_by = '-';
                }

                $doc_name = !empty($val['doctor_name']) ? preg_replace('/\s*\([^)]*\)$/', '', trim($val['doctor_name'])) : '-';
                if (empty($doc_name)) {
                    $doc_name = '-';
                }

                $tot = (float)$val['total'];
                $disc = (float)$val['discount'];
                $tax = (float)$val['tax'];
                $net = (float)$val['net_amount'];
                $paid = (float)$val['paid_amount'];
                $ref = (float)$val['refund_amount'];
                $bal = $net - $paid + $ref;

                $total_amount         += $tot;
                $total_discount       += $disc;
                $total_tax            += $tax;
                $total_net_amount     += $net;
                $total_paid_amount    += $paid;
                $total_refund_amount  += $ref;
                $total_balance_amount += $bal;

                $discount_pct = ($tot != 0) ? ($disc * 100) / $tot : 0;
                $tax_pct = (($tot - $disc) != 0) ? ($tax * 100) / ($tot - $disc) : 0;

                $print_rows[] = array(
                    'bill_no'        => $bill_no,
                    'case_id'        => $case_id,
                    'patient_name'   => $p_name,
                    'generated_by'   => $gen_by,
                    'doctor_name'    => $doc_name,
                    'amount'         => number_format($tot, 2),
                    'discount'       => number_format($disc, 2) . ' (' . number_format($discount_pct, 2) . '%)',
                    'tax'            => number_format($tax, 2) . ' (' . number_format($tax_pct, 2) . '%)',
                    'net_amount'     => number_format($net, 2),
                    'paid_amount'    => number_format($paid, 2),
                    'refund_amount'  => number_format($ref, 2),
                    'balance_amount' => number_format($bal, 2),
                );
            }
        }

        $data['hospital_name']        = $hospital_name;
        $data['report_subtitle']      = $report_subtitle;
        $data['print_rows']           = $print_rows;
        $data['total_amount']         = $total_amount;
        $data['total_discount']       = $total_discount;
        $data['total_tax']            = $total_tax;
        $data['total_net_amount']     = $total_net_amount;
        $data['total_paid_amount']    = $total_paid_amount;
        $data['total_refund_amount']  = $total_refund_amount;
        $data['total_balance_amount'] = $total_balance_amount;
        $data['currency_symbol']      = $this->customlib->getHospitalCurrencyFormat();

        $html = $this->load->view('admin/report/_printBalanceAmountReport', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }

    public function incomeexpensebalancereport()
    {
        if (!$this->rbac->hasPrivilege('income_expense_balance_report', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/finance');
        $this->session->set_userdata('subsub_menu', 'admin/report/incomeexpensebalancereport');

        $search_type = $this->input->post('search_type', TRUE);
        if (empty($search_type)) {
            $search_type = 'this_month';
        }
        $data['search_type'] = $search_type;
        $today               = $this->customlib->YYYYMMDDTodateFormat(date('Y-m-d'));
        $data['date_from']   = $this->input->post('date_from', TRUE) ?: $today;
        $data['date_to']     = $this->input->post('date_to', TRUE) ?: $today;

        $start_date = null;
        $end_date   = null;

        if ($search_type == 'today') {
            $start_date = date('Y-m-d');
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'this_week') {
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date   = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($search_type == 'last_week') {
            $start_date = date('Y-m-d', strtotime('monday last week'));
            $end_date   = date('Y-m-d', strtotime('sunday last week'));
        } elseif ($search_type == 'this_month') {
            $start_date = date('Y-m-01');
            $end_date   = date('Y-m-t');
        } elseif ($search_type == 'last_month') {
            $start_date = date('Y-m-01', strtotime('first day of last month'));
            $end_date   = date('Y-m-t', strtotime('last day of last month'));
        } elseif ($search_type == 'last_3_month') {
            $start_date = date('Y-m-d', strtotime('-3 months'));
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'last_6_month') {
            $start_date = date('Y-m-d', strtotime('-6 months'));
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'last_12_month') {
            $start_date = date('Y-m-d', strtotime('-12 months'));
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'last_year') {
            $start_date = date('Y-m-d', strtotime('first day of january last year'));
            $end_date   = date('Y-m-d', strtotime('last day of december last year'));
        } elseif ($search_type == 'this_year') {
            $start_date = date('Y-01-01');
            $end_date   = date('Y-12-31');
        } elseif ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($data['date_from']);
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($data['date_to']);
            // If dates are invalid (year missing or format mismatch), reset to null
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$start_date) ||
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$end_date)) {
                $start_date = null;
                $end_date   = null;
            }
        }
        // all_time: start_date and end_date remain null (no date filter)

        $data['report_data'] = $this->report_model->getIncomeExpenseBalanceReport($start_date, $end_date);

        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/incomeexpensebalancereport', $data);
        $this->load->view('layout/footer', $data);
    }

    public function print_incomeexpensebalance_report()
    {
        if (!$this->rbac->hasPrivilege('income_expense_balance_report', 'can_view')) {
            echo json_encode(array('status' => 'fail', 'message' => 'Access Denied'));
            return;
        }

        $search_type = $this->input->post('search_type', TRUE);
        if (empty($search_type)) {
            $search_type = 'this_month';
        }

        $today     = $this->customlib->YYYYMMDDTodateFormat(date('Y-m-d'));
        $date_from = $this->input->post('date_from', TRUE) ?: $today;
        $date_to   = $this->input->post('date_to', TRUE) ?: $today;

        $start_date = null;
        $end_date   = null;

        if ($search_type == 'today') {
            $start_date = date('Y-m-d');
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'this_week') {
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date   = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($search_type == 'last_week') {
            $start_date = date('Y-m-d', strtotime('monday last week'));
            $end_date   = date('Y-m-d', strtotime('sunday last week'));
        } elseif ($search_type == 'this_month') {
            $start_date = date('Y-m-01');
            $end_date   = date('Y-m-t');
        } elseif ($search_type == 'last_month') {
            $start_date = date('Y-m-01', strtotime('first day of last month'));
            $end_date   = date('Y-m-t', strtotime('last day of last month'));
        } elseif ($search_type == 'last_3_month') {
            $start_date = date('Y-m-d', strtotime('-3 months'));
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'last_6_month') {
            $start_date = date('Y-m-d', strtotime('-6 months'));
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'last_12_month') {
            $start_date = date('Y-m-d', strtotime('-12 months'));
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'last_year') {
            $start_date = date('Y-m-d', strtotime('first day of january last year'));
            $end_date   = date('Y-m-d', strtotime('last day of december last year'));
        } elseif ($search_type == 'this_year') {
            $start_date = date('Y-01-01');
            $end_date   = date('Y-12-31');
        } elseif ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($date_from);
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($date_to);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$start_date) ||
                !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$end_date)) {
                $start_date = null;
                $end_date   = null;
            }
        }

        $report_data = $this->report_model->getIncomeExpenseBalanceReport($start_date, $end_date);

        $hospital_name = 'YOUR HOSPITAL NAME';
        if (isset($this->setting_model)) {
            $h_details = $this->setting_model->getHospitalDetails();
            if (!empty($h_details) && !empty($h_details->name)) {
                $hospital_name = $h_details->name;
            }
        }

        $search_type_labels = array(
            'today'         => 'Today',
            'this_week'     => 'This Week',
            'last_week'     => 'Last Week',
            'this_month'    => 'This Month',
            'last_month'    => 'Last Month',
            'last_3_month'  => 'Last 3 Months',
            'last_6_month'  => 'Last 6 Months',
            'last_12_month' => 'Last 12 Months',
            'last_year'     => 'Last Year',
            'this_year'     => 'This Year',
            'all_time'      => 'All Time',
            'period'        => 'Period'
        );

        if ($search_type == 'period' && !empty($start_date) && !empty($end_date)) {
            $report_subtitle = "Income Expense Balance Report [Period From: " . date('d-M-Y', strtotime($start_date)) . " To: " . date('d-M-Y', strtotime($end_date)) . "]";
        } else {
            $lbl = isset($search_type_labels[$search_type]) ? $search_type_labels[$search_type] : ucfirst(str_replace('_', ' ', $search_type));
            $report_subtitle = "Income Expense Balance Report [Duration: " . $lbl . "]";
        }

        $print_rows    = array();
        $total_income  = 0;
        $total_expense = 0;

        if (!empty($report_data)) {
            foreach ($report_data as $row) {
                $inc = (float)$row['income_in'];
                $exp = (float)$row['expense_out'];
                $bal = $inc - $exp;

                $total_income  += $inc;
                $total_expense += $exp;

                $date_disp = (!empty($row['record_date']) && $row['record_date'] != '0000-00-00')
                    ? date('d-M-Y', strtotime($row['record_date']))
                    : '-';

                $print_rows[] = array(
                    'date'        => $date_disp,
                    'type'        => $row['inc_exp_head'],
                    'income_in'   => number_format($inc, 2),
                    'expense_out' => number_format($exp, 2),
                    'balance'     => number_format($bal, 2),
                    'balance_val' => $bal,
                );
            }
        }

        $net_balance = $total_income - $total_expense;

        $data['hospital_name']   = $hospital_name;
        $data['report_subtitle'] = $report_subtitle;
        $data['print_rows']      = $print_rows;
        $data['total_income']    = $total_income;
        $data['total_expense']   = $total_expense;
        $data['net_balance']     = $net_balance;
        $data['currency_symbol'] = $this->customlib->getHospitalCurrencyFormat();

        $html = $this->load->view('admin/report/_printIncomeExpenseBalanceReport', $data, true);
        echo json_encode(array('status' => 'success', 'html' => $html));
    }

    public function salereturns()
    {
        if (!$this->rbac->hasPrivilege('pharmacy_bill', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/pharmacy');
        $this->session->set_userdata('subsub_menu', 'reports/report/salereturns');
        $prefix_array          = $this->session->userdata('hospitaladmin')['prefix'];
        $data['prefix_bill']   = $this->customlib->getSessionPrefixByType('pharmacy_billing');
        $data['prefix_return'] = isset($prefix_array['pharmacy_return']) ? $prefix_array['pharmacy_return'] : 'RET';
        $data['module'] = 'reports';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/report/sale_return_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getsalereturnreportdata()
    {
        if (!$this->rbac->hasPrivilege('pharmacy_bill', 'can_view')) {
            access_denied();
        }
        $search_type  = $this->input->post('search_type', TRUE);
        $patient_name = $this->input->post('patient_name', TRUE);

        $prefix_array  = $this->session->userdata('hospitaladmin')['prefix'];
        $prefix_bill   = $this->customlib->getSessionPrefixByType('pharmacy_billing');
        $prefix_return = isset($prefix_array['pharmacy_return']) ? $prefix_array['pharmacy_return'] : 'RET';

        $bill_no   = $this->input->post('bill_no', TRUE);
        $return_no = $this->input->post('return_no', TRUE);

        if (!empty($prefix_bill) && stripos($bill_no, $prefix_bill) === 0) {
            $bill_no = substr($bill_no, strlen($prefix_bill));
        }
        if (!empty($prefix_return) && stripos($return_no, $prefix_return) === 0) {
            $return_no = substr($return_no, strlen($prefix_return));
        }

        $start_date = null;
        $end_date   = null;

        if ($search_type == 'today') {
            $start_date = date('Y-m-d');
            $end_date   = date('Y-m-d');
        } elseif ($search_type == 'this_week') {
            $start_date = date('Y-m-d', strtotime('monday this week'));
            $end_date   = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($search_type == 'last_week') {
            $start_date = date('Y-m-d', strtotime('monday last week'));
            $end_date   = date('Y-m-d', strtotime('sunday last week'));
        } elseif ($search_type == 'this_month') {
            $start_date = date('Y-m-01');
            $end_date   = date('Y-m-t');
        } elseif ($search_type == 'last_month') {
            $start_date = date('Y-m-01', strtotime('first day of last month'));
            $end_date   = date('Y-m-t', strtotime('last day of last month'));
        } elseif ($search_type == 'this_year') {
            $start_date = date('Y-01-01');
            $end_date   = date('Y-12-31');
        } elseif ($search_type == 'period') {
            $start_date = $this->customlib->dateFormatToYYYYMMDD($this->input->post('date_from', TRUE));
            $end_date   = $this->customlib->dateFormatToYYYYMMDD($this->input->post('date_to', TRUE));
        }

        $php_format   = $this->customlib->getHospitalDateFormat();
        $mysql_format = str_replace(['d', 'm', 'Y'], ['%d', '%m', '%Y'], $php_format);
        echo $this->report_model->getSaleReturnReport($start_date, $end_date, $bill_no, $return_no, $patient_name, $mysql_format);
    }

    public function getPatientListAjax()
    {
        if (!$this->rbac->hasPrivilege('patient', 'can_view')) {
            access_denied();
        }
        $search_term = $this->input->post("searchTerm");
        if (isset($search_term) && $search_term != '') {
            $result = $this->patient_model->getPatientListfilter($search_term);
            $data   = array();
            if (!empty($result)) {

                foreach ($result as $value) {
                    $data[] = array("id" => $value->id, "text" => $value->patient_name . " (" . $value->id . ")");
                }
            }
            echo json_encode($data);
        }
    }




}