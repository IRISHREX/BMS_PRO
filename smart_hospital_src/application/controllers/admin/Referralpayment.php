<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Referralpayment extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('notificationsetting_model');

        $this->load->model("referral_payment_model");
        $this->load->model("referral_person_model");
        $this->load->library("form_validation");
        $this->load->library('system_notification');
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_add')) {
            access_denied();
        }

        $data = array();
        $this->form_validation->set_rules("patient_id", $this->lang->line('patient'), 'required|trim|xss_clean');
        $this->form_validation->set_rules("payee", $this->lang->line('payee'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("percentage", $this->lang->line('commission_percentage'), 'required|trim|xss_clean');
        $this->form_validation->set_rules("commission_amount", $this->lang->line('commission_amount'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("patient_type", $this->lang->line('patient_type'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("bill_amount", $this->lang->line('patient_bill_amount'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("bill_no", $this->lang->line('bill_no_case_id'), 'trim|required|xss_clean|callback_check_billid');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'patient_id'        => form_error('patient_id'),
                'payee'             => form_error('payee'),
                'percentage'        => form_error('percentage'),
                'commission_amount' => form_error('commission_amount'),
                'percentage'        => form_error('percentage'),
                'patient_type'      => form_error('patient_type'),
                'bill_amount'       => form_error('bill_amount'),
                'bill_no'           => form_error('bill_no'),

            );
            $data = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $payment = array(
                "referral_person_id" => $this->input->post("payee", TRUE),
                "patient_id"         => $this->input->post("patient_id", TRUE),
                "referral_type"      => $this->input->post("patient_type", TRUE),
                "billing_id"         => $this->input->post("bill_no", TRUE),
                "bill_amount"        => $this->input->post("bill_amount", TRUE),
                "percentage"         => $this->input->post("percentage", TRUE),
                "amount"             => $this->input->post("commission_amount", TRUE),
                "status"             => $this->input->post("status", TRUE) ? $this->input->post("status", TRUE) : 'Unpaid',
                "date"               => $this->input->post("entry_date", TRUE) ? $this->customlib->dateFormatToYYYYMMDDHis($this->input->post("entry_date", TRUE), $this->customlib->getHospitalTimeFormat()) : (new DateTime('now', new DateTimeZone($this->customlib->getTimeZone() ? $this->customlib->getTimeZone() : 'UTC')))->format('Y-m-d H:i:s'),
            );

            $this->referral_payment_model->add($payment);
            $data = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));

            $referral_type   = $this->notificationsetting_model->getreferraltypeDetails($this->input->post("patient_type", TRUE));
            $referral_person = $this->notificationsetting_model->getreferralpersonDetails($this->input->post("payee", TRUE));

            $event_data = array(
                'patient_id'            => $this->input->post("patient_id", TRUE),
                'patient_type'          => $this->lang->line($referral_type['name']),
                'bill_no'               => $this->customlib->getSessionPrefixByType($referral_type['prefixes_type']) . $this->input->post('bill_no', TRUE),
                'patient_bill_amount'   => number_format((float) $this->input->post("bill_amount", TRUE), 2, '.', ''),
                'payee'                 => $referral_person['name'],
                'commission_percentage' => $this->input->post("percentage", TRUE),
                'commission_amount'     => $this->input->post("commission_amount", TRUE),
            );

            $this->system_notification->send_system_notification('add_referral_payment', $event_data);
        }
        echo json_encode($data);
    }

    public function paySingle()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_edit')) {
            access_denied();
        }
        $id = $this->input->post('id', TRUE);
        $result = $this->referral_payment_model->mark_as_paid($id);
        echo json_encode($result);
    }

    public function payAll()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_edit')) {
            access_denied();
        }
        $result = $this->referral_payment_model->pay_all_eligible();
        echo json_encode(array(
            'status'  => true,
            'message' => "Paid {$result['paid_count']} eligible referral(s). " . ($result['skipped_count'] > 0 ? "({$result['skipped_count']} skipped due to pending patient bill balance)." : ""),
            'data'    => $result
        ));
    }

    public function updateSettings()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_edit')) {
            access_denied();
        }
        $auto_pay = $this->input->post('referral_auto_pay', TRUE) ? 1 : 0;
        $reminder_time = $this->input->post('referral_reminder_time', TRUE);
        $reminder_time = $reminder_time ? $reminder_time : '09:00:00';

        $this->referral_payment_model->update_referral_settings(array(
            'referral_auto_pay'      => $auto_pay,
            'referral_reminder_time' => $reminder_time,
        ));

        echo json_encode(array('status' => 'success', 'message' => $this->lang->line('success_message')));
    }

    public function sendReminder()
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_view')) {
            access_denied();
        }
        $unpaid = $this->referral_payment_model->get_unpaid_referrals();
        $total_unpaid = count($unpaid);
        $total_amount = 0;
        foreach ($unpaid as $u) {
            $total_amount += (float)$u['amount'];
        }

        $event_data = array(
            'total_unpaid_count'  => $total_unpaid,
            'total_unpaid_amount' => number_format($total_amount, 2),
            'date'                => date('Y-m-d H:i:s'),
        );

        $this->system_notification->send_system_notification('referral_payment_reminder', $event_data);

        echo json_encode(array(
            'status'  => 'success',
            'message' => "Reminder sent! Found {$total_unpaid} unpaid referral(s) totaling " . number_format($total_amount, 2) . "."
        ));
    }

    public function check_billid()
    {
        $billing_id = $this->input->post('bill_no', TRUE);
        $check      = $this->referral_payment_model->check_billid($billing_id);
        if ($check > 0) {
            $this->form_validation->set_message('check_billid', $this->lang->line('referral_payment_already_generated_for_this_bill_no'));
            return false;
        } else {
            return true;
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('referral_payment', 'can_delete')) {
            access_denied();
        }
        if (!empty($id)) {
            $this->referral_payment_model->delete($id);
            echo json_encode(array("status" => 1, "msg" => $this->lang->line("delete_message")));
        }
    }

    public function get($id)
    {
        $data = $this->referral_payment_model->get($id);
        if (!empty($data) && isset($data['date'])) {
            $data['formatted_date'] = $this->customlib->YYYYMMDDHisTodateFormat($data['date'], $this->customlib->getHospitalTimeFormat());
        }
        echo json_encode($data);
    }

    public function update()
    {
        $data = array();
        $this->form_validation->set_rules("commission_percentage", $this->lang->line('commission_percentage'), 'trim|required|xss_clean');
        $this->form_validation->set_rules("commission_amount", $this->lang->line('commission_amount'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $msg = array(
                "commission_percentage" => form_error('commission_percentage'),
                "commission_amount"     => form_error('commission_amount'),
            );
            $data = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $payment = array(
                "id"         => $this->input->post('paymentid', TRUE),
                "percentage" => $this->input->post('commission_percentage', TRUE),
                "amount"     => $this->input->post('commission_amount', TRUE),
                "status"     => $this->input->post('edit_status', TRUE) ? $this->input->post('edit_status', TRUE) : 'Unpaid',
                "date"       => $this->input->post('edit_entry_date', TRUE) ? $this->customlib->dateFormatToYYYYMMDDHis($this->input->post('edit_entry_date', TRUE), $this->customlib->getHospitalTimeFormat()) : (new DateTime('now', new DateTimeZone($this->customlib->getTimeZone() ? $this->customlib->getTimeZone() : 'UTC')))->format('Y-m-d H:i:s'),
            );

            $this->referral_payment_model->update($payment);
            $data = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($data);
    }

    public function getCommission()
    {
        $type       = $this->input->post("type", TRUE);
        $payee      = $this->input->post("payee", TRUE);
        $percentage = $this->referral_payment_model->get_commission($payee, $type);
		 
        echo $percentage;
    }

    public function getBillNo()
    {
        $referral_type = $this->input->post('type', TRUE);
        $patient_id    = $this->input->post('patient_id', TRUE);
        if ($referral_type == 1) {
            //opd
            $result = $this->referral_payment_model->get_opdBillNo($patient_id);
        } elseif ($referral_type == 2) {
            //ipd
            $result = $this->referral_payment_model->get_ipdBillNo($patient_id);
        } elseif ($referral_type == 3) {
            //pharmacy
            $result = $this->referral_payment_model->get_pharmacyBillNo($patient_id);
        } elseif ($referral_type == 4) {
            //pathology
            $result = $this->referral_payment_model->get_pathologyBillNo($patient_id);
        } elseif ($referral_type == 5) {
            //radiology
            $result = $this->referral_payment_model->get_radiologyBillNo($patient_id);
        } elseif ($referral_type == 6) {
            //blood_bank
            $result = $this->referral_payment_model->get_bloodbankBillNo($patient_id);
        } elseif ($referral_type == 7) {
            //ambulance
            $result = $this->referral_payment_model->get_ambulanceBillNo($patient_id);
        }
        echo json_encode($result);
    }

    public function getBillAmount()
    {
        $referral_type = $this->input->post('type', TRUE);
        $bill_no       = $this->input->post('bill_no', TRUE);
        if ($referral_type == 1) {
            //opd
            $result = $this->referral_payment_model->get_opdBillAmount($bill_no);

        } elseif ($referral_type == 2) {
            //ipd
            $result = $this->referral_payment_model->get_ipdBillAmount($bill_no);
        } elseif ($referral_type == 3) {
            //pharmacy
            $result = $this->referral_payment_model->get_pharmacyBillAmount($bill_no);
        } elseif ($referral_type == 4) {
            //pathology
            $result = $this->referral_payment_model->get_pathologyBillAmount($bill_no);
        } elseif ($referral_type == 5) {
            //radiology
            $result = $this->referral_payment_model->get_radiologyBillAmount($bill_no);
        } elseif ($referral_type == 6) {
            //blood_bank
            $result = $this->referral_payment_model->get_bloodbankBillAmount($bill_no);
        } elseif ($referral_type == 7) {
            //ambulance
            $result = $this->referral_payment_model->get_ambulanceBillAmount($bill_no);
        }

        echo json_encode($result);
    }

}
