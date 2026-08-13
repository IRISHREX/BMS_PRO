<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}


class License_status extends Public_Controller
{
  
    public function smarthospital()
    {
        return $this->respond($this->license_service->loadLicense());
    }

   
    public function mobileapp()
    {
        if (!$this->isActive($this->license_service->loadLicense())) {
            return $this->emit(403, 'failed');
        }
        return $this->respond($this->license_service->loadAndroidLicense());
    }


    public function addon($product_id = null)
    {
        if (!$this->isActive($this->license_service->loadLicense())) {
            return $this->emit(403, 'failed');
        }
        if ($product_id === null || $product_id === '') {
            return $this->emit(403, 'failed');
        }
        return $this->respond($this->license_service->loadAddonLicense($product_id));
    }

 
    private function isActive($block)
    {
        return is_array($block)
            && !empty($block['licenseKey'])
            && !empty($block['signingKey'])
            && !empty($block['productCode'])
            && isset($block['lic_status'])
            && $block['lic_status'] === 'ACTIVE';
    }

    private function respond($block)
    {
        try {
            return $this->isActive($block)
                ? $this->emit(200, 'success')
                : $this->emit(403, 'failed');
        } catch (Exception $e) {
            return $this->emit(500, 'failed');
        }
    }

    private function emit($code, $status)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => $status)));
    }
}
