<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/** QR/barcode check-in uses staff_attendance so manual attendance and reports remain the single source of truth. */
class Qrattendance_model extends CI_Model
{
    private $settings_table = 'qr_attendance_settings';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('staffattendancemodel');
    }

    public function ensureSettingsTable()
    {
        if (!$this->db->table_exists($this->settings_table)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$this->settings_table}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `auto_attendance` tinyint(1) NOT NULL DEFAULT 1,
                `camera_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `scanner_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `camera_facing_mode` varchar(20) NOT NULL DEFAULT 'environment',
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if ($this->db->table_exists($this->settings_table) && $this->db->count_all($this->settings_table) === 0) {
            $this->db->insert($this->settings_table, array('updated_at' => date('Y-m-d H:i:s')));
        }
    }

    public function getSettings()
    {
        $this->ensureSettingsTable();
        return $this->db->get($this->settings_table)->row_array();
    }

    public function saveSettings($data)
    {
        $this->ensureSettingsTable();
        $settings = $this->getSettings();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $settings['id'])->update($this->settings_table, $data);
    }

    public function findStaffByScanCode($code)
    {
        $code = trim($code);
        $this->db->select('staff.id, staff.employee_id, staff.name, staff.surname, staff.image, roles.name AS role_name')
            ->from('staff')
            ->join('staff_roles', 'staff_roles.staff_id = staff.id', 'left')
            ->join('roles', 'roles.id = staff_roles.role_id', 'left')
            ->where('staff.is_active', 1)
            ->group_start()
            ->where('staff.employee_id', $code)
            ->or_where('staff.id', $code)
            ->group_end();
        $staff = $this->db->get()->row_array();

        // Generated card payloads commonly include the employee id with a separator.
        if (!$staff && preg_match('/[A-Za-z0-9_-]+/', $code, $matches)) {
            $tokens = preg_split('/[^A-Za-z0-9_-]+/', $code);
            foreach (array_reverse($tokens) as $token) {
                if ($token === '') {
                    continue;
                }
                $this->db->select('staff.id, staff.employee_id, staff.name, staff.surname, staff.image, roles.name AS role_name')
                    ->from('staff')->join('staff_roles', 'staff_roles.staff_id = staff.id', 'left')
                    ->join('roles', 'roles.id = staff_roles.role_id', 'left')
                    ->where('staff.is_active', 1)->where('staff.employee_id', $token);
                $staff = $this->db->get()->row_array();
                if ($staff) {
                    break;
                }
            }
        }
        return $staff;
    }

    public function punch($staff)
    {
        if (!$this->db->table_exists('staff_attendance')) {
            return array('status' => 0, 'message' => 'Staff attendance table is missing. Run the application database update.');
        }

        $today = date('Y-m-d');
        $now   = date('H:i:s');
        $has_qr_source = $this->db->field_exists('qrcode_attendance', 'staff_attendance');
        $attendance = $this->db->where('staff_id', $staff['id'])->where('date', $today)
            ->order_by('id', 'desc')->get('staff_attendance')->row_array();

        // First punch. We deliberately do not depend on role schedules: manual attendance
        // uses the same Present default and every active staff member can use the terminal.
        if (!$attendance) {
        $present = $this->db->select('id')->where('key_value', 'present')->where('is_active', 'yes')
            ->get('staff_attendance_type')->row_array();
            if (!$present) {
                return array('status' => 0, 'message' => 'No active Present attendance type is configured.');
            }
            $insert = array(
                'staff_id' => $staff['id'],
                'date' => $today,
                'staff_attendance_type_id' => $present['id'],
                'remark' => '',
                'is_active' => 1,
                'in_time' => $now,
                'created_at' => date('Y-m-d H:i:s'),
            );
            if ($has_qr_source) {
                $insert['qrcode_attendance'] = 1;
            }
            if (!$this->db->insert('staff_attendance', $insert)) {
                $error = $this->db->error();
                return array('status' => 0, 'message' => 'Attendance could not be saved: ' . (!empty($error['message']) ? $error['message'] : 'database write failed.'));
            }
            $attendance = $this->db->where('id', $this->db->insert_id())->get('staff_attendance')->row_array();
            return array('status' => 1, 'message' => 'Checked in successfully.', 'attendance' => $attendance);
        }

        if (!empty($attendance['in_time']) && !empty($attendance['out_time'])) {
            return array('status' => 0, 'message' => 'Today\'s check-in and check-out are already recorded.');
        }
        $update = empty($attendance['in_time']) ? array('in_time' => $now) : array('out_time' => $now);
        if ($has_qr_source) {
            $update['qrcode_attendance'] = 1;
        }
        if (!$this->db->where('id', $attendance['id'])->update('staff_attendance', $update)) {
            $error = $this->db->error();
            return array('status' => 0, 'message' => 'Attendance could not be saved: ' . (!empty($error['message']) ? $error['message'] : 'database write failed.'));
        }
        $attendance = $this->db->where('id', $attendance['id'])->get('staff_attendance')->row_array();
        return array('status' => 1, 'message' => empty($attendance['out_time']) ? 'Checked in successfully.' : 'Checked out successfully.', 'attendance' => $attendance);
    }
}
