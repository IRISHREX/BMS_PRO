<div class="row"><div class="col-md-12"><div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fa fa-qrcode"></i> QR Code Attendance Settings</h3></div>
    <form method="post" action="<?php echo site_url('admin/qrattendance/setting/index'); ?>">
        <div class="card-body">
            <?php echo $this->customlib->getCSRF(); ?>
            <p class="text-muted">QR punches use the existing staff attendance records, including check-in, check-out and attendance reports.</p>
            <div class="form-group"><label><input type="checkbox" name="auto_attendance" value="1" <?php echo $settings['auto_attendance'] ? 'checked' : ''; ?>> Enable QR / barcode attendance</label></div>
            <div class="form-group"><label><input type="checkbox" name="scanner_enabled" value="1" <?php echo $settings['scanner_enabled'] ? 'checked' : ''; ?>> Enable USB / sensor-based barcode scanners</label></div>
            <div class="form-group"><label><input type="checkbox" name="camera_enabled" value="1" <?php echo $settings['camera_enabled'] ? 'checked' : ''; ?>> Enable camera-based QR scanning</label></div>
            <div class="form-group"><label>Camera</label><div><label class="radio-inline"><input type="radio" name="camera_facing_mode" value="environment" <?php echo $settings['camera_facing_mode'] !== 'user' ? 'checked' : ''; ?>> Primary (back)</label><label class="radio-inline"><input type="radio" name="camera_facing_mode" value="user" <?php echo $settings['camera_facing_mode'] === 'user' ? 'checked' : ''; ?>> Secondary (front)</label></div></div>
        </div>
        <div class="card-footer text-right"><a href="<?php echo site_url('admin/qrattendance/attendance/index'); ?>" class="btn btn-default">Cancel</a> <button class="btn btn-primary" type="submit">Save</button></div>
    </form>
</div></div></div>
