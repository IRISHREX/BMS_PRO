<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Referral_payment_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        try {
            if (!$this->db->field_exists('status', 'referral_payment')) {
                $this->db->query("ALTER TABLE `referral_payment` ADD `status` varchar(20) NOT NULL DEFAULT 'Unpaid'");
            } else {
                $this->db->query("ALTER TABLE `referral_payment` MODIFY `status` varchar(20) NOT NULL DEFAULT 'Unpaid'");
            }
            if (!$this->db->field_exists('paid_date', 'referral_payment')) {
                $this->db->query("ALTER TABLE `referral_payment` ADD `paid_date` datetime NULL DEFAULT NULL");
            }
            if (!$this->db->field_exists('paid_by', 'referral_payment')) {
                $this->db->query("ALTER TABLE `referral_payment` ADD `paid_by` int(11) NULL DEFAULT NULL");
            }
            if (!$this->db->field_exists('referral_auto_pay', 'sch_settings')) {
                $this->db->query("ALTER TABLE `sch_settings` ADD `referral_auto_pay` tinyint(1) NOT NULL DEFAULT 0");
            }
            if (!$this->db->field_exists('referral_reminder_time', 'sch_settings')) {
                $this->db->query("ALTER TABLE `sch_settings` ADD `referral_reminder_time` time NOT NULL DEFAULT '09:00:00'");
            }
        } catch (Throwable $e) {
            log_message('error', 'Referral_payment_model: DB migration error: ' . $e->getMessage());
        }
    }

    public function get_bill_settlement($billing_id, $referral_type)
    {
        $net_amount  = 0;
        $paid_amount = 0;

        if ($referral_type == 4) { // Pathology
            $row = $this->db->select('net_amount')->where('id', $billing_id)->get('pathology_billing')->row_array();
            $net_amount = $row ? (float)$row['net_amount'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('pathology_billing_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        } elseif ($referral_type == 5) { // Radiology
            $row = $this->db->select('net_amount')->where('id', $billing_id)->get('radiology_billing')->row_array();
            $net_amount = $row ? (float)$row['net_amount'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('radiology_billing_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        } elseif ($referral_type == 3) { // Pharmacy
            $row = $this->db->select('net_amount')->where('id', $billing_id)->get('pharmacy_bill_basic')->row_array();
            $net_amount = $row ? (float)$row['net_amount'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('pharmacy_bill_basic_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        } elseif ($referral_type == 6) { // Blood Bank
            $row = $this->db->select('net_amount')->where('id', $billing_id)->get('blood_issue')->row_array();
            $net_amount = $row ? (float)$row['net_amount'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('blood_issue_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        } elseif ($referral_type == 7) { // Ambulance
            $row = $this->db->select('net_amount')->where('id', $billing_id)->get('ambulance_call')->row_array();
            $net_amount = $row ? (float)$row['net_amount'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('ambulance_call_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        } elseif ($referral_type == 1) { // OPD
            $row = $this->db->select('IFNULL(SUM(apply_charge),0) as net_amt')->where('opd_id', $billing_id)->get('patient_charges')->row_array();
            $net_amount = $row ? (float)$row['net_amt'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('opd_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        } elseif ($referral_type == 2) { // IPD
            $row = $this->db->select('IFNULL(SUM(apply_charge),0) as net_amt')->where('ipd_id', $billing_id)->get('patient_charges')->row_array();
            $net_amount = $row ? (float)$row['net_amt'] : 0;

            $paid_row = $this->db->select('IFNULL(SUM(CASE WHEN type="payment" THEN amount WHEN type="refund" THEN -amount ELSE 0 END),0) as paid_amt')
                ->where('ipd_id', $billing_id)->get('transactions')->row_array();
            $paid_amount = $paid_row ? (float)$paid_row['paid_amt'] : 0;
        }

        $balance    = max(0, round($net_amount - $paid_amount, 2));
        $is_settled = ($net_amount > 0 && $balance <= 0.001);

        return array(
            'net_amount'  => $net_amount,
            'paid_amount' => $paid_amount,
            'balance'     => $balance,
            'is_settled'  => $is_settled
        );
    }

    public function get_payment()
    {
        $this->db->select("payment.date as date, payment.paid_date, payment.paid_by, payment.billing_id, payment.id, payment.status, payment.referral_type, person.name, person.contact, patients.patient_name, patients.id as patient_id, type.name as type, payment.bill_amount, payment.percentage, payment.amount, prefixes.prefix");
        $this->db->join("referral_type type", "type.id=payment.referral_type", "left");
        $this->db->join("prefixes", "type.prefixes_type=prefixes.type", "inner");
        $this->db->join("referral_person person", "person.id=payment.referral_person_id");
        $this->db->join("patients", "patients.id=payment.patient_id", "left");
        $this->db->order_by("payment.id", "desc");
        $query   = $this->db->get("referral_payment payment");
        $payment = $query->result_array();

        foreach ($payment as $key => $val) {
            $settlement = $this->get_bill_settlement($val['billing_id'], $val['referral_type']);
            $current_net = $settlement['net_amount'];
            $current_commission = round(($current_net * (float)$val['percentage']) / 100, 2);

            $payment[$key]['bill_amount']  = $current_net;
            $payment[$key]['amount']       = $current_commission;
            $payment[$key]['bill_net']     = $current_net;
            $payment[$key]['bill_paid']    = $settlement['paid_amount'];
            $payment[$key]['bill_balance'] = $settlement['balance'];
            $payment[$key]['is_settled']   = $settlement['is_settled'];
        }

        return $payment;
    }

    public function mark_as_paid($id, $staff_id = null)
    {
        $payment = $this->get($id);
        if (empty($payment)) {
            return array('status' => false, 'message' => 'Record not found.');
        }

        if (strtolower($payment['status']) === 'paid') {
            return array('status' => false, 'message' => 'Referral commission is already paid.');
        }

        $settlement = $this->get_bill_settlement($payment['billing_id'], $payment['referral_type']);
        if (!$settlement['is_settled']) {
            return array(
                'status'  => false,
                'message' => 'Cannot mark as paid. Patient bill is not fully settled yet (Due Balance: ' . number_format($settlement['balance'], 2) . ').'
            );
        }

        $current_net = $settlement['net_amount'];
        $current_commission = round(($current_net * (float)$payment['percentage']) / 100, 2);

        $data = array(
            'id'          => $id,
            'bill_amount' => $current_net,
            'amount'      => $current_commission,
            'status'      => 'Paid',
            'paid_date'   => date('Y-m-d H:i:s'),
            'paid_by'     => $staff_id ? $staff_id : $this->customlib->getLoggedInUserID(),
        );

        $this->update($data);
        return array('status' => true, 'message' => 'Referral commission marked as Paid successfully.');
    }

    public function pay_all_eligible($staff_id = null)
    {
        $unpaid = $this->db->where('status !=', 'Paid')->or_where('status IS NULL', null, false)->get('referral_payment')->result_array();
        $paid_count    = 0;
        $skipped_count = 0;

        foreach ($unpaid as $row) {
            $settlement = $this->get_bill_settlement($row['billing_id'], $row['referral_type']);
            if ($settlement['is_settled']) {
                $current_net = $settlement['net_amount'];
                $current_commission = round(($current_net * (float)$row['percentage']) / 100, 2);
                $data = array(
                    'id'          => $row['id'],
                    'bill_amount' => $current_net,
                    'amount'      => $current_commission,
                    'status'      => 'Paid',
                    'paid_date'   => date('Y-m-d H:i:s'),
                    'paid_by'     => $staff_id ? $staff_id : $this->customlib->getLoggedInUserID(),
                );
                $this->update($data);
                $paid_count++;
            } else {
                $skipped_count++;
            }
        }

        return array(
            'paid_count'    => $paid_count,
            'skipped_count' => $skipped_count,
            'total_unpaid'  => count($unpaid),
        );
    }

    public function get_unpaid_referrals()
    {
        $this->db->select("payment.date as date, payment.billing_id, payment.id, payment.status, payment.referral_type, person.name, person.contact, patients.patient_name, patients.id as patient_id, type.name as type, payment.bill_amount, payment.percentage, payment.amount, prefixes.prefix");
        $this->db->join("referral_type type", "type.id=payment.referral_type", "left");
        $this->db->join("prefixes", "type.prefixes_type=prefixes.type", "inner");
        $this->db->join("referral_person person", "person.id=payment.referral_person_id");
        $this->db->join("patients", "patients.id=payment.patient_id", "left");
        $this->db->where("payment.status !=", "Paid");
        $this->db->or_where("payment.status IS NULL", null, false);
        $this->db->order_by("payment.id", "desc");
        $query = $this->db->get("referral_payment payment");
        $list  = $query->result_array();

        foreach ($list as $key => $val) {
            $settlement = $this->get_bill_settlement($val['billing_id'], $val['referral_type']);
            $current_net = $settlement['net_amount'];
            $current_commission = round(($current_net * (float)$val['percentage']) / 100, 2);

            $list[$key]['bill_amount']  = $current_net;
            $list[$key]['amount']       = $current_commission;
            $list[$key]['bill_net']     = $current_net;
            $list[$key]['bill_paid']    = $settlement['paid_amount'];
            $list[$key]['bill_balance'] = $settlement['balance'];
            $list[$key]['is_settled']   = $settlement['is_settled'];
        }

        return $list;
    }

    public function get_referral_settings()
    {
        $row = $this->db->select('referral_auto_pay, referral_reminder_time')->get('sch_settings')->row_array();
        return array(
            'referral_auto_pay'       => isset($row['referral_auto_pay']) ? (int)$row['referral_auto_pay'] : 0,
            'referral_reminder_time' => isset($row['referral_reminder_time']) ? $row['referral_reminder_time'] : '09:00:00',
        );
    }

    public function update_referral_settings($data)
    {
        return $this->db->update('sch_settings', $data);
    }
 
    public function add($payment)
    {        
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        
        $this->db->insert('referral_payment', $payment);
        
        $insert_id = $this->db->insert_id();
        $message = INSERT_RECORD_CONSTANT . " On Referral Payment id " . $insert_id;
        $action = "Insert";
        $record_id = $insert_id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {            
            return $record_id;
        }        
    }

    public    function deleteByBillId($billing_id, $referral_type)
    {
        $this->db->where('billing_id', $billing_id)->where('referral_type', $referral_type)->delete('referral_payment');
    }

    public function delete($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id)->delete('referral_payment');        
        $message = DELETE_RECORD_CONSTANT . " On Referral Payment id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $record_id;
        }        
    }

    public function get($id)
    {
        $payment = $this->db->select()->where('id', $id)->get("referral_payment")->row_array();
        return $payment;
    }

    public function update($payment)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start=========================== 

        $this->db->where('id', $payment['id'])->update("referral_payment", $payment);
        
        $message = UPDATE_RECORD_CONSTANT . " On Referral Payment id " . $payment['id'];
        $action = "Update";
        $record_id = $payment['id'];
        $this->log($message, $record_id, $action);
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            return $record_id;
        }         
    }

    public function get_commission($payee, $type)
    {
        $this->db->select("commission.commission");
        $this->db->where(array("referral_person_id" => $payee, "referral_type_id" => $type));
        $query  = $this->db->get("referral_person_commission commission");
        $result = $query->row_array();
		if(!empty($result)){
			return $result["commission"];
		}else{
			return false;
		}
    }

    public function getPatientBill($patient_id)
    {
        $this->db->select("amount");
        $this->db->where("patient_name", $patient_id);
        $query  = $this->db->get("ambulance_call");
        $result = $query->row_array();
        return $result["amount"];
    }

    public function get_opdBillNo($patient_id){
        return $this->db->select('opd_details.id as bill_no,opd_details.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="opd_no") as prefixe_name,(select prefixes.prefix from prefixes where prefixes.type="opd_no") as prefixe_name')->from('opd_details')->where(array('patient_id'=>$patient_id,'discharged'=>'no'))->get()->result_array();
    }

    public function get_ipdBillNo($patient_id){
        return $this->db->select('ipd_details.id as bill_no,ipd_details.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="ipd_no") as prefixe_name')->from('ipd_details')->where(array('patient_id'=>$patient_id,'discharged'=>'no'))->get()->result_array();
    }

    public function get_pharmacyBillNo($patient_id){
        return $this->db->select('pharmacy_bill_basic.id as bill_no,pharmacy_bill_basic.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="pharmacy_billing") as prefixe_name')->from('pharmacy_bill_basic')->where(array('patient_id'=>$patient_id))->get()->result_array();
    }

    public function get_pathologyBillNo($patient_id){
        return $this->db->select('pathology_billing.id as bill_no,pathology_billing.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="pathology_billing") as prefixe_name')->from('pathology_billing')->where(array('patient_id'=>$patient_id))->get()->result_array();
    }

    public function get_radiologyBillNo($patient_id){
        return $this->db->select('radiology_billing.id as bill_no,radiology_billing.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="radiology_billing") as prefixe_name')->from('radiology_billing')->where(array('patient_id'=>$patient_id))->get()->result_array();
    }

    public function get_bloodbankBillNo($patient_id){
        return $this->db->select('blood_issue.id as bill_no,blood_issue.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="blood_bank_billing") as prefixe_name')->from('blood_issue')->where(array('patient_id'=>$patient_id))->get()->result_array();
    }

    public function get_ambulanceBillNo($patient_id){
        return $this->db->select('ambulance_call.id as bill_no,ambulance_call.case_reference_id as case_id,(select prefixes.prefix from prefixes where prefixes.type="ambulance_call_billing") as prefixe_name')->from('ambulance_call')->where(array('patient_id'=>$patient_id))->get()->result_array();
    }
 
    public function get_opdBillAmount($bill_no){
        return $this->db->select('sum(`amount`) as total_bill')->from('patient_charges')->where('opd_id',$bill_no)->group_by('opd_id')->get()->row_array();
    }

    public function get_ipdBillAmount($bill_no){
       return $this->db->select('sum(`amount`) as total_bill')->from('patient_charges')->where('ipd_id',$bill_no)->group_by('ipd_id')->get()->row_array();
    }

    public function get_pharmacyBillAmount($bill_no){
        return $this->db->select('net_amount as total_bill')->from('pharmacy_bill_basic')->where(array('id'=>$bill_no))->get()->row_array();
    }

    public function get_pathologyBillAmount($bill_no){       
       return $this->db->select('pathology_billing.net_amount as total_bill')->from('pathology_billing')->where('id',$bill_no)->get()->row_array();
    }
    
    public function get_radiologyBillAmount($bill_no){
         return $this->db->select('radiology_billing.net_amount as total_bill')->from('radiology_billing')->where('id',$bill_no)->get()->row_array();
    }
    
    public function get_bloodbankBillAmount($bill_no){
        return $this->db->select('net_amount as total_bill')->from('blood_issue')->where(array('id'=>$bill_no))->get()->row_array();
    }

    public function get_ambulanceBillAmount($bill_no){
        return $this->db->select('net_amount as total_bill')->from('ambulance_call')->where(array('id'=>$bill_no))->get()->row_array();
    }

    public function check_billid($billing_id){
        $query = $this->db->select('billing_id')->where('billing_id', $billing_id)->get('referral_payment');
        return $query->num_rows();
    }
}
