<?php

define('THEMES_DIR', 'themes');
define('BASE_URI', str_replace('index.php', '', $_SERVER['SCRIPT_NAME']));

class MY_Controller extends CI_Controller
{

    protected $langs = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $lang_array = array();
		$this->load->library('Db_manager');
        $this->load->helper('lang');
        $this->load->helper('language');
        $this->load->config('license');
		$this->load->library('License_service');

        $shlk = $this->license_service->loadLicense();
        if (is_array($shlk)) {
            $this->config->set_item('SHLK', $shlk);
            if (isset($shlk['lic_status'])) {
                $this->config->set_item('lic_status', $shlk['lic_status']);
            }
        }

        $addon_status = array();

        foreach ($this->license_service->listAddons() as $product_id => $block) {
            if (is_array($block) && !empty($block['lic_status'])) {
                // Key by short_name so addonchk() resolves without a DB lookup.
                $status_key = !empty($block['short_name']) ? $block['short_name'] : $product_id;
                $addon_status[$status_key] = $block['lic_status'];
            }
        }
        $this->config->set_item('addon_status', $addon_status);

        $android = $this->license_service->loadAndroidLicense();

        if (is_array($android) && !empty($android['lic_status'])) {
            $this->config->set_item('android_app_status', $android['lic_status']);
        }	   
        $this->load->library('auth');
        $this->load->library('module_lib');
        $this->load->helper('directory');
        $this->load->helper('custom_helper');
        $this->load->helper('mime_helper');
		
        $this->load->helper('mask');
		
        $this->load->model(array('setting_model','chatuser_model','ambulance_model'));
  
        if ($this->session->has_userdata('hospitaladmin')) {
            $admin    = $this->session->userdata('hospitaladmin');
            $language = ($admin['language']['language']);
        } else if ($this->session->has_userdata('patient')) {
            $student  = $this->session->userdata('patient');
            $language = ($student['language']['language']);
        } else {
            $sss      = $this->setting_model->get();
			$language = $sss[0]['language'];
        }
 
        $this->config->set_item('language', $language);
        $hospital_tz = $this->customlib->getTimeZone();
        if (!empty($hospital_tz)) {
            @date_default_timezone_set($hospital_tz);
        }
        $map = directory_map(APPPATH . "./language/" . $language . "/app_files");
        foreach ($map as $lang_key => $lang_value) {
            $lang_array[] = 'app_files/' . str_replace(".php", "", $lang_value);
        }
        $this->load->language($lang_array, $language);

        // RTL flag — exposed globally to every view so headers can swap CSS
        // and emit dir="rtl" without each controller having to set it.
        $rtl_mode = ($this->customlib->getRTL() === 'yes');
        $this->load->vars(['rtl_mode' => $rtl_mode]);

        // Theme Studio tokens — merged hospital defaults + per-user overrides.
        // Re-resolved per request (no session caching) so superadmin brand
        // changes propagate immediately to all users without their own prefs.
        $admin_sess   = $this->session->userdata('hospitaladmin');
        $patient_sess = $this->session->userdata('patient');
        if (!empty($admin_sess) && !empty($admin_sess['id'])) {
            $sh_theme_tokens = theme_resolve_tokens($admin_sess['id'], 'staff');
        } elseif (!empty($patient_sess) && !empty($patient_sess['patient_id'])) {
            $sh_theme_tokens = theme_resolve_tokens($patient_sess['patient_id'], 'patient');
        } else {
            $sh_theme_tokens = theme_resolve_tokens();
        }
        $this->load->vars(['sh_theme_tokens' => $sh_theme_tokens]);
    }

}

class Admin_Controller extends MY_Controller
{
  protected $chat_count = 0;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('rbac');
        $this->auth->is_logged_in();
        $this->check_license();       
    }
  
    public function check_license($force = false)
    {
        $shlk = $this->license_service->loadLicense();
        if (!is_array($shlk) || empty($shlk['licenseKey']) || empty($shlk['signingKey']) || empty($shlk['productCode'])) {
            $this->update_sh_routine();
            return false;
        }

        // Auto-deregister if the code has been copied to a different domain.
        if (!$this->license_service->domainMatches($shlk)) {
            $this->update_sh_routine();
            return false;
        }


        if (!$force && !$this->license_service->needsVerify()) {
            return isset($shlk['lic_status']) && $shlk['lic_status'] === 'ACTIVE';
        }

        $result = $this->call_verify_api($shlk);

        if ($result['httpcode'] == 201 && isset($result['body']['valid'])
            && $result['body']['valid'] === true
            && isset($result['body']['status']) && $result['body']['status'] === 'ACTIVE'
        ) {
            $shlk['lic_status'] = $result['body']['status'];
            $this->license_service->saveLicense($shlk);
            $this->config->set_item('SHLK', $this->license_service->loadLicense());
            $this->config->set_item('lic_status', $result['body']['status']);
            return true;
        }

        if ($result['httpcode'] == 201 && isset($result['body']['valid']) && $result['body']['valid'] !== true) {
            $this->update_sh_routine();
            return false;
        }

        if ($this->license_service->inGracePeriod()) {
            return true;
        }

        $this->update_sh_routine();
        return false;
    }

    public function check_addon_license($product_id)
    {
        $block = $this->license_service->loadAddonLicense($product_id);
        if (!is_array($block) || empty($block['licenseKey']) || empty($block['signingKey']) || empty($block['productCode'])) {
            $this->clear_addon_state($product_id);
            return false;
        }

        // Auto-deregister if the code has been copied to a different domain.
        if (!$this->license_service->domainMatches($block)) {
            $this->clear_addon_state($product_id);
            return false;
        }

        if (!$this->license_service->needsVerify($product_id)) {
            return true;
        }

        $result = $this->call_verify_api($block);

        if ($result['httpcode'] == 201 && isset($result['body']['valid'])
            && $result['body']['valid'] === true
            && isset($result['body']['status']) && $result['body']['status'] === 'ACTIVE'
        ) {
            $block['lic_status'] = $result['body']['status'];
            $this->license_service->saveAddonLicense($product_id, $block);
            $addon_status = $this->config->item('addon_status');
            if (!is_array($addon_status)) {
                $addon_status = array();
            }
            $status_key = !empty($block['short_name']) ? $block['short_name'] : $product_id;
            $addon_status[$status_key] = $result['body']['status'];
            $this->config->set_item('addon_status', $addon_status);
            return true;
        }

        if ($result['httpcode'] == 201 && isset($result['body']['valid']) && $result['body']['valid'] !== true) {
            $this->clear_addon_state($product_id);
            return false;
        }

        if ($this->license_service->inGracePeriod($product_id)) {
            return true;
        }

        $this->clear_addon_state($product_id);
        return false;
    }

    public function check_android_license($force = false)
    {
        $block = $this->license_service->loadAndroidLicense();
        if (!is_array($block) || empty($block['licenseKey']) || empty($block['signingKey']) || empty($block['productCode'])) {
            $this->clear_android_state();
            return false;
        }

        // Auto-deregister if the code has been copied to a different domain.
        if (!$this->license_service->domainMatches($block)) {
            $this->clear_android_state();
            return false;
        }


        if (!$force && !$this->license_service->needsAndroidVerify()) {
            return true;
        }
							   

        $result = $this->call_verify_api($block);

        if ($result['httpcode'] == 201 && isset($result['body']['valid'])
            && $result['body']['valid'] === true
            && isset($result['body']['status']) && $result['body']['status'] === 'ACTIVE'
        ) {
            $block['lic_status'] = $result['body']['status'];
            $this->license_service->saveAndroidLicense($block);
            $this->config->set_item('android_app_status', $result['body']['status']);
            return true;
        }

        if ($result['httpcode'] != 0 && $result['httpcode'] < 500) {
            $this->clear_android_state();
            return false;
        }

        if ($this->license_service->androidInGracePeriod()) {
            return true;
        }

        $this->clear_android_state();
        return false;
    }

    private function clear_android_state()
    {
        $this->license_service->clearAndroidLicense();
        $this->config->set_item('android_app_status', '');
    }

    private function call_verify_api(array $block)
    {
        $licenseKeyHash = $block['licenseKey'];
        if (!preg_match('/^[a-f0-9]{64}$/i', $licenseKeyHash)) {
            $licenseKeyHash = hash('sha256', $licenseKeyHash);
        }

        $domain = base_url();
        $domain = preg_replace('/^https?:\/\//i', '', $domain);
        $domain = rtrim($domain, '/');
        $domain = strtolower($domain);

        $timestamp = (string)time();
        $signature = hash_hmac('sha256', $licenseKeyHash . $block['productCode'] . $domain . $timestamp, $block['signingKey']);

        $data = array(
            'licenseKeyHash' => $licenseKeyHash,
            'productCode'    => $block['productCode'],
            'domain'         => $domain,
            'timestamp'      => $timestamp,
            'signature'      => $signature,
        );
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, "https://apigt.webfeb.com/api/v1/licenses/verify");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $output   = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'httpcode' => $httpcode,
            'body'     => json_decode($output, true),
        );
    }

    private function clear_addon_state($product_id)
    {
        // Resolve the short_name (the addon_status key) from the block before clearing it.
        $block = $this->license_service->loadAddonLicense($product_id);
        $status_key = (is_array($block) && !empty($block['short_name'])) ? $block['short_name'] : $product_id;
        $this->license_service->clearAddonLicense($product_id);
        $addon_status = $this->config->item('addon_status');
        if (is_array($addon_status) && isset($addon_status[$status_key])) {
            unset($addon_status[$status_key]);
            $this->config->set_item('addon_status', $addon_status);
        }
    }

    public function update_sh_routine()
    {
        $this->license_service->clearLicense();
        $this->config->set_item('SHLK', '');
        $this->config->set_item('lic_status', '');
    }

}

class Patient_Controller extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->config('payroll');
        $this->auth->is_logged_in_user('patient');
    }

}

class Public_Controller extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

}

class Parent_Controller extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->auth->is_logged_in_user('parent');
    }

}

class Front_Controller extends CI_Controller
{
    public $setting_model;
    public $frontcms_setting_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('lang');
        $this->load->helper('language');
        $this->check_installation();
        if ($this->config->item('installed') == true) {
            $this->db->reconnect();
        }

        $this->load->model('frontcms_setting_model');
        if (empty($this->frontcms_setting_model)) {
            $this->load->model('Frontcms_setting_model', 'frontcms_setting_model');
        }
        $this->front_setting = ($this->frontcms_setting_model) ? $this->frontcms_setting_model->get() : (object)array('is_active_front_cms' => 0, 'is_active_online_appointment' => 0, 'theme' => 'default');
		$front_cms_class  = $this->router->fetch_class();
        $front_cms_method = $this->router->fetch_method();

            if ($this->front_setting->is_active_front_cms) {
                $this->config->set_item('front_layout', true);
            }
			
            if (!$this->front_setting->is_active_front_cms) {
                $this->config->set_item('front_layout', false);
            }

            if(!$this->front_setting->is_active_front_cms && !$this->front_setting->is_active_online_appointment){              
                 redirect('site/userlogin');
            }  

           if($this->front_setting->is_active_online_appointment){
                if (!$this->front_setting->is_active_front_cms && 
                    !($front_cms_class == "welcome" && $front_cms_method == "appointment") 
                ) {
                    redirect('site/userlogin');
                }
            }

        $this->theme_path = $this->front_setting->theme;
    
      //================
        $language = ($this->school_details->language);
        $this->config->set_item('language', $language);
        $this->load->helper('directory');
        $lang_array = array('form_validation_lang');
        $map        = directory_map(APPPATH . "./language/" . $language . "/app_files");
        foreach ($map as $lang_key => $lang_value) {
            $lang_array[] = 'app_files/' . str_replace(".php", "", $lang_value);
        }        
   
        $this->load->language($lang_array, $language);
      //===============
        $this->load->config('ci-blog');
    }

    protected function load_theme($content = null, $layout = true)
    {
        $this->data['main_menus']     = '';
        $this->data['school_setting'] = $this->school_details;
        $this->data['front_setting']  = $this->front_setting;
        $menu_list                    = $this->cms_menu_model->getBySlug('main-menu');
        $footer_menu_list             = $this->cms_menu_model->getBySlug('bottom-menu');
        if (count($menu_list) > 0) {
            $this->data['main_menus'] = $this->cms_menuitems_model->getMenus($menu_list['id']);
        }

        if (count($footer_menu_list) > 0) {
            $this->data['footer_menus'] = $this->cms_menuitems_model->getMenus($footer_menu_list['id']);
        }
        $this->data['layout_type']   = $layout;
        $this->data['theme_color']   = isset($this->front_setting->theme_color)  ? $this->front_setting->theme_color  : 'aurora';
        $this->data['home_layout']   = isset($this->front_setting->home_layout)  ? $this->front_setting->home_layout  : 'hospital';
        $raw_sections = isset($this->front_setting->home_section_data) ? $this->front_setting->home_section_data : '';
        $this->data['home_sections'] = $raw_sections ? json_decode($raw_sections, true) : array();
        $this->data['home_sections'] = resolve_hero_media($this->data['home_sections'], $this->data['home_layout']);
        $raw_static = isset($this->front_setting->static_pages_data) ? $this->front_setting->static_pages_data : '';
        $this->data['static_pages']  = $raw_static ? json_decode($raw_static, true) : array();
        $this->data['header'] = $this->load->view('themes/' . $this->theme_path . '/header', $this->data, true);
        $this->data['slider'] = $this->load->view('themes/' . $this->theme_path . '/home_slider', $this->data, true);
        $this->data['footer'] = $this->load->view('themes/' . $this->theme_path . '/footer', $this->data, true);
        $this->base_assets_url = 'backend/' . THEMES_DIR . '/' . $this->theme_path . '/';

        $this->data['base_assets_url'] = BASE_URI . $this->base_assets_url;
        
        $is_captcha                  = $this->captchalib->is_captcha('appointment');
        $this->data["is_captcha"]    = $is_captcha;
        if($is_captcha){
            $captcha_result = $this->captchalib->generate_captcha();
        $this->data["captcha_image"] = is_array($captcha_result) ? $captcha_result['image'] : '';
        }
        if ($layout == true) {
            $this->data['content'] = (is_null($content)) ? '' : $this->load->view(THEMES_DIR . '/' . $this->theme_path . '/' . $content, $this->data, true);
            $this->load->view(THEMES_DIR . '/' . $this->theme_path . '/layout', $this->data);
        } else {
            $this->data['content'] = (is_null($content)) ? '' : $this->load->view(THEMES_DIR . '/' . $this->theme_path . '/' . $content, $this->data, true);
            $this->load->view(THEMES_DIR . '/' . $this->theme_path . '/base_layout', $this->data);
        }        
    }

    protected function load_theme_form($content = null, $layout = true)
    {
        $this->data['main_menus']     = '';
        $this->data['school_setting'] = $this->school_details;
        $this->data['front_setting']  = $this->front_setting;
        $menu_list                    = $this->cms_menu_model->getBySlug('main-menu');
        $footer_menu_list             = $this->cms_menu_model->getBySlug('bottom-menu');
        if (count($menu_list) > 0) {
            $this->data['main_menus'] = $this->cms_menuitems_model->getMenus($menu_list['id']);
        }

        if (count($footer_menu_list) > 0) {
            $this->data['footer_menus'] = $this->cms_menuitems_model->getMenus($footer_menu_list['id']);
        }
        $this->data['theme_color']   = isset($this->front_setting->theme_color)  ? $this->front_setting->theme_color  : 'aurora';
        $this->data['home_layout']   = isset($this->front_setting->home_layout)  ? $this->front_setting->home_layout  : 'hospital';
        $raw_sections = isset($this->front_setting->home_section_data) ? $this->front_setting->home_section_data : '';
        $this->data['home_sections'] = $raw_sections ? json_decode($raw_sections, true) : array();
        $this->data['home_sections'] = resolve_hero_media($this->data['home_sections'], $this->data['home_layout']);
        $raw_static = isset($this->front_setting->static_pages_data) ? $this->front_setting->static_pages_data : '';
        $this->data['static_pages']  = $raw_static ? json_decode($raw_static, true) : array();
        $this->data['header'] = $this->load->view('themes/' . $this->theme_path . '/header', $this->data, true);
        $this->data['slider'] = $this->load->view('themes/' . $this->theme_path . '/home_slider', $this->data, true);
        $this->data['footer'] = $this->load->view('themes/' . $this->theme_path . '/footer', $this->data, true);
        $this->base_assets_url = 'backend/' . THEMES_DIR . '/' . $this->theme_path . '/';

        $this->data['base_assets_url'] = BASE_URI . $this->base_assets_url;

        $is_captcha                  = $this->captchalib->is_captcha('appointment');
        $this->data["is_captcha"]    = $is_captcha;
        $captcha_result = $this->captchalib->generate_captcha();
        $this->data["captcha_image"] = is_array($captcha_result) ? $captcha_result['image'] : '';
        $this->data['content']       = (is_null($content)) ? '' : $this->load->view(THEMES_DIR . '/' . $this->theme_path . '/' . $content, $this->data, true);

        $this->load->view(THEMES_DIR . '/' . $this->theme_path . '/layout', $this->data);
    }

    private function check_installation()
    {
        if ($this->uri->segment(1) !== 'install') {
            $this->load->config('migration');
            if ($this->config->item('installed') == false && $this->config->item('migration_enabled') == false) {
                $proto    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $app_root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
                header('Location: ' . $proto . '://' . $_SERVER['HTTP_HOST'] . $app_root . 'install/start');
                exit();
            } else {
                if (is_dir(APPPATH . 'controllers/install')) {
                    echo '<h3>Delete the install folder from application/controllers/install</h3>';
                    die;
                }
            }
        }
    }

    public function __get($key)
    {
        $CI = &get_instance();
        if (isset($CI->$key)) {
            return $CI->$key;
        }

        // Auto-load model dynamically on first access if property ends in _model
        if (substr($key, -6) === '_model' || file_exists(APPPATH . 'models/' . ucfirst($key) . '.php')) {
            $this->load->model($key);
            if (isset($CI->$key)) {
                return $CI->$key;
            }
        }

        return null;
    }

}
