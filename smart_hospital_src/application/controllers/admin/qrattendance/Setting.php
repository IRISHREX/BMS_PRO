<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Setting extends Admin_Controller
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
    public function index()
    {
        $this->authorize();
        $this->session->set_userdata('top_menu', 'qrattendance');
        $this->session->set_userdata('sub_menu', 'admin/qrattendance/setting/index');
        if ($this->input->method(TRUE) === 'POST') {
            $this->qrattendance_model->saveSettings(array(
                'auto_attendance' => $this->input->post('auto_attendance', true) ? 1 : 0,
                'camera_enabled' => $this->input->post('camera_enabled', true) ? 1 : 0,
                'scanner_enabled' => $this->input->post('scanner_enabled', true) ? 1 : 0,
                'camera_facing_mode' => $this->input->post('camera_facing_mode', true) === 'user' ? 'user' : 'environment',
            ));
            $this->session->set_flashdata('msg', '<div class="alert alert-success">QR attendance settings saved.</div>');
            redirect('admin/qrattendance/setting/index');
        }
        $data['title'] = 'QR Code Attendance Settings';
        $data['settings'] = $this->qrattendance_model->getSettings();
        $data['module'] = 'human_resource';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/qrattendance/settings', $data);
        $this->load->view('layout/footer', $data);
    }
}
