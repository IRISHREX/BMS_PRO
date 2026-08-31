<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Attendance extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('qrattendance_model');
    }

    private function authorize()
    {
        if (!$this->rbac->hasPrivilege('staff_attendance', 'can_view')) access_denied();
    }

    private function json($data)
    {
        // Keep consecutive scanner punches working when CSRF token regeneration is enabled.
        $data['csrf_hash'] = $this->security->get_csrf_hash();
        return $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }

    public function index()
    {
        $this->authorize();
        $this->session->set_userdata('top_menu', 'qrattendance');
        $this->session->set_userdata('sub_menu', 'admin/qrattendance/attendance/index');
        $data['title'] = 'QR Code Attendance';
        $data['settings'] = $this->qrattendance_model->getSettings();
        $data['module'] = 'human_resource';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/qrattendance/attendance', $data);
        $this->load->view('layout/footer', $data);
    }

    public function scan()
    {
        try {
        $this->authorize();
        $settings = $this->qrattendance_model->getSettings();
        if (!$settings['auto_attendance']) {
            return $this->json(array('status' => 2, 'message' => 'QR attendance is currently disabled in settings.'));
        }
        $code = trim((string) $this->input->post('code', true));
        if ($code === '' || strlen($code) > 255) {
            return $this->json(array('status' => 0, 'message' => 'Scan a valid staff ID QR code or barcode.'));
        }
        $staff = $this->qrattendance_model->findStaffByScanCode($code);
        if (!$staff) {
            return $this->json(array('status' => 0, 'message' => 'No active staff member matches this card.'));
        }
        $result = $this->qrattendance_model->punch($staff);
        $result['staff'] = $staff;
        return $this->json($result);
        } catch (Throwable $exception) {
            log_message('error', 'QR attendance scan failure: ' . $exception->getMessage());
            return $this->json(array('status' => 0, 'message' => 'Attendance could not be saved: ' . $exception->getMessage()));
        }
    }
}
