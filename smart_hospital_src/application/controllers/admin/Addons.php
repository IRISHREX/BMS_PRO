<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Addons extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('addon_helper');
        $this->load->model('addons_model');
    }

    public function addonUpdateCheck()
    {
        $results = [];

        foreach ($this->license_service->listAddons() as $product_id => $block) {
            $addon_info = $this->addons_model->getByProductID($block['productCode']);


            if (!$addon_info || IsNullOrEmptyString($addon_info->current_version)) {
                continue;
            }
            $currentVersion = $addon_info->current_version;
            $domain = base_url();
            $domain = preg_replace('/^https?:\/\//i', '', $domain);
            $domain = rtrim($domain, '/');
            $domain = strtolower($domain);
            $payload = [
                'licenseKeyHash' => $block['licenseKey'],
                'productCode'    => $block['productCode'],
                'domain'         => $domain,
                'currentVersion' => $currentVersion,
                'signingKey'     => $block['signingKey'],
            ];

            $response = $this->auth->addon_update_check($payload);
            $httpcode = isset($response['httpcode']) ? $response['httpcode'] : 0;
            $body     = isset($response['body']) ? $response['body'] : null;

            $entry = [
                'product_id'      => $product_id,
                'name'            => $addon_info ? $addon_info->name : '',
                'currentVersion'  => $currentVersion,
                'updateAvailable' => false,
                'checked'         => false,
            ];

            if ($httpcode == 200 && is_array($body)) {
                $entry['checked']         = true;
                $entry['updateAvailable'] = !empty($body['updateAvailable']);

                if ($entry['updateAvailable']) {
                    $entry['latestVersion'] = isset($body['latestVersion']) ? $body['latestVersion'] : '';
                    $entry['releaseNotes']  = isset($body['releaseNotes']) ? $body['releaseNotes'] : '';
                    $entry['downloadUrl']   = isset($body['downloadUrl']) ? $body['downloadUrl'] : '';
                    $entry['source']        = 'online';
                }
            } else {

                $entry['error'] = is_array($body) && isset($body['message'])
                    ? $body['message']
                    : 'Update check failed (HTTP ' . $httpcode . ').';
            }

            if (!$entry['updateAvailable']) {
                $localVersions = $this->addons_model->getAddonVersions($addon_info->id, $currentVersion);
                if (!empty($localVersions)) {
                    $latestLocal = end($localVersions); // ordered by version_order ASC
                    $entry['updateAvailable'] = true;
                    $entry['latestVersion']   = $latestLocal->version;
                    $entry['source']          = 'local';
                }
            }

            $results[] = $entry;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => 1,
                'addons'  => $results,
            ]));
    }


    public function downloadUpdate()
    {
        $product_id   = $this->input->post('product_id');
        $addon_folder = $this->input->post('addon');
		
		$domain = base_url();
        $domain = preg_replace('/^https?:\/\//i', '', $domain);
        $domain = rtrim($domain, '/');
        $domain = strtolower($domain);
			
			
        if (empty($product_id) || empty($addon_folder)) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Missing update parameters.']);
        }

        // The addon must be installed (current_version set) and licensed.
        $addon_info = $this->addons_model->getByProductID($product_id);
        if (!$addon_info || IsNullOrEmptyString($addon_info->current_version)) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Addon is not installed.']);
        }

        $block = $this->license_service->loadAddonLicense($product_id);
        if (!is_array($block) || empty($block['licenseKey']) || empty($block['signingKey'])) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Addon license not found. Please register again.']);
        }

        // Re-check for an update server-side to obtain the (fresh) download URL.
        $check = $this->auth->addon_update_check([
            'licenseKeyHash' => $block['licenseKey'],
            'productCode'    => isset($block['productCode']) ? $block['productCode'] : $product_id,
            'domain'         => $domain,
            'currentVersion' => $addon_info->current_version,
            'signingKey'     => $block['signingKey'],
        ]);

        $checkBody = isset($check['body']) ? $check['body'] : null;
        if (!isset($check['httpcode']) || $check['httpcode'] != 200 || !is_array($checkBody)) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Could not check for updates. Please try again.']);
        }
        if (empty($checkBody['updateAvailable'])) {
            return $this->jsonOut(['status' => 0, 'uptodate' => 1, 'msg' => 'This addon is already up to date.']);
        }

        $download_url = isset($checkBody['downloadUrl']) ? $checkBody['downloadUrl'] : '';
        if (empty($download_url)) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Update is available but no download file was provided by the server.']);
        }

        $base = 'https://apigt.webfeb.com';
        $url  = (stripos($download_url, 'http') === 0) ? $download_url : $base . $download_url;

        logMessage("//================= Online update download for " . $addon_folder . " started =================//");

        // 1. Download the zip.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $zip_data     = curl_exec($ch);
        $httpcode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpcode != 200 || empty($zip_data)) {
            logMessage("Online update download failed (HTTP " . $httpcode . ").");
            return $this->jsonOut(['status' => 0, 'msg' => 'Failed to download the update package.']);
        }
        // A JSON body means the server returned an error instead of a zip.
        if (stripos($content_type, 'json') !== false) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Update server returned an error.']);
        }

        // 2. Save under ./addons/<directory>.zip so the unzip folder matches the addon.
        $addons_path = FCPATH . "./addons/";
        if (!is_dir($addons_path)) {
            mkdir($addons_path, 0755, true);
        }
        $zip_path = $addons_path . $addon_folder . ".zip";
        if (file_put_contents($zip_path, $zip_data) === false) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Could not write the update package to disk.']);
        }

        $folder_name = strtolower($addon_folder);

        // 3. Validate the package in a temp dir (must be an 'updater' for an already-installed addon).
        $zip = new ZipArchive;
        if ($zip->open($zip_path) !== TRUE) {
            unlink($zip_path);
            return $this->jsonOut(['status' => 0, 'msg' => 'Downloaded update package is not a valid zip.']);
        }
        $extract_to_temp_path = $addons_path . "temp/" . $folder_name;
        rmTempDir($extract_to_temp_path);
        $zip->extractTo($extract_to_temp_path);
        $zip->close();

        $list_details     = listInnerFolders($extract_to_temp_path);
        $installer_exists = is_dir($addons_path . $folder_name . "/installer");

        if (!is_array($list_details) || !in_array('updater', $list_details) || !$installer_exists) {
            rmTempDir($extract_to_temp_path);
            unlink($zip_path);
            return $this->jsonOut(['status' => 0, 'msg' => 'Update package is not a valid updater for this addon.']);
        }

        // 4. Extract into ./addons/<folder> and register the new versions.
        $zip2 = new ZipArchive;
        if ($zip2->open($zip_path) === TRUE) {
            $zip2->extractTo($addons_path . $folder_name);
            $zip2->close();
        }
        $return_json = read_json($addons_path . $folder_name . "/addon_info.json");
        $this->addons_model->addAddonSetupDetails($return_json);

        rmTempDir($extract_to_temp_path);
        if (file_exists($zip_path)) {
            unlink($zip_path);
        }

        // 5. Apply the update (installed addon -> updateVersion path).
        $addon_details = $this->addons_model->getAddonWithVersion($product_id);
        if (!$addon_details) {
            return $this->jsonOut(['status' => 0, 'msg' => 'Addon not found after download.']);
        }

        logMessage("//================= Online update applying for " . $addon_folder . " =================//");
        return $this->updateVersion($addon_details);
    }

    private function jsonOut($data)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($data));
    }	 
    public function index()
    {
        
        $this->session->set_userdata('top_menu', 'setup');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $this->session->set_userdata('inner_menu', 'admin/addons/index');
        $data['title']      = 'Addons';
        
        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_handle_upload');
        if ($this->form_validation->run() == true) {

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                logMessage("//=================Addon Folder uploading started.=================//");
                $check_folder_exists = FCPATH . "./addons/";
                if (!is_dir($check_folder_exists)) {
                    if (mkdir($check_folder_exists, 0755, true)) {
                        logMessage("Addons Folder not exist and has been created on root directory.");
                    } else {
                        logMessage("Addons Folder not exist and could not be created on root directory.");
                    }
                }


                $fileInfo = pathinfo($_FILES["file"]["name"]);
                if (move_uploaded_file($_FILES["file"]["tmp_name"], "./addons/" . $_FILES["file"]["name"])) {
                    logMessage("New addon Uploading started on Addons Folder.");
                    $file_name = $_FILES['file']['name'];
                    $target_folder =   FCPATH . "./addons/" . basename($file_name);
                    $folder_name               = strtolower(pathinfo(basename($file_name), PATHINFO_FILENAME));
                    // Create a ZipArchive instance
                    $zip_tmp = new ZipArchive;
                    // Open the ZIP file
                    if ($zip_tmp->open($target_folder) === TRUE) {
                        $extract_to_temp_path =  FCPATH . "./addons/temp/" . $folder_name;
                        rmTempDir($extract_to_temp_path);
                        $zip_tmp->extractTo($extract_to_temp_path);
                        $zip_tmp->close();
                        $parameters =  $extract_to_temp_path . '||' . $folder_name;

                        $this->form_validation->set_rules('file', $this->lang->line('file'), 'callback_check_valid_zip[' . $parameters . ']');

                        if ($this->form_validation->run() == true) {
                            $zip = new ZipArchive;
                            if ($zip->open($target_folder) === TRUE) {

                                logMessage("Starting the unzip the addon folder.");
                                // Extract the contents to the specified directory
                                $extract_path =  FCPATH . "./addons/" . $folder_name;
                                $zip->extractTo($extract_path);
                                $zip->close();
                                logMessage("File unzipped successfully to:" . $extract_path);
                                $return_json = read_json(FCPATH . "./addons/" . $folder_name . "/addon_info.json");
                                $data_inserted = $this->addons_model->addAddonSetupDetails($return_json);
                                if ($data_inserted) {
                                    logMessage("inserted addon info details to addons table");
                                    rmTempDir($extract_to_temp_path); // remove addon folder from temp after check 
                                    unlink($target_folder); //remove uploaded zip file 
                                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                                    redirect('admin/addons/index');
                                } else {
                                    logMessage("Error executing query.");
                                }
                            }
                        } else {
                            rmTempDir($extract_to_temp_path); // remove addon folder from temp after check 
                            unlink($target_folder); //remove uploaded zip file 
                        }
                    } else {
                        logMessage("Failed to unzip the addon folder.");
                    }
                } else {
                    logMessage("Failed to upload the addon folder.");
                }
            }
        }



        $data['module'] = 'addons';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/addons/index', $data);
        $this->load->view('layout/footer', $data);
    }



    function check_valid_zip($filed, $extract_path)
    {

        $params = explode('||', $extract_path);

        $list_details = listInnerFolders($params[0]);


        if (!empty($list_details)) {
            $check_folder_exists = FCPATH . "./addons/" . $params[1] . "/installer";
            if (!in_array('updater', $list_details) xor !in_array('installer', $list_details)) {
                if (in_array('installer', $list_details)) {

                    if (is_dir($check_folder_exists)) {
                        $this->form_validation->set_message('check_valid_zip', 'The addon has already installer files.');
                        return FALSE;
                    }
                } else if (in_array('updater', $list_details)) {

                    if (!is_dir($check_folder_exists)) {
                        $this->form_validation->set_message('check_valid_zip', 'The addon does not contain an installer files.');
                        return FALSE;
                    }
                }
            } else {
                $this->form_validation->set_message('check_valid_zip', 'Zip file does contain valid files.');
                return FALSE;
            }
        }
        return true;
    }

    public function install()
    {
        $copy_folder = false;
        $install_sql = false;
        $addon_folder = $this->input->post('addon', TRUE);
        $product_id = $this->input->post('product_id', TRUE);
        $addon_details = $this->addons_model->getAddonWithVersion($product_id);

        if (IsNullOrEmptyString($addon_details->current_version)) {
            $this->installNew($addon_folder, $product_id, $addon_details);
        } else {
            $this->updateVersion($addon_details);
        }
    }

    public function uninstall()
    {
        $copy_folder = false;
        $install_sql = false;
        $addon_folder = $this->input->post('addon', TRUE);
        $product_id = $this->input->post('product_id', TRUE);
        $addon_details = $this->addons_model->getAddonWithVersion($product_id);

        logMessage("*************************************************************************************************************");
        $uninstall_db =  FCPATH . "./addons/" . $addon_folder . "/uninstall/db.sql";
        $delete_file_json_path =  FCPATH . "./addons/" . $addon_folder . "/uninstall/unistall_directory.json";

        logMessage("//================= Addon " . $addon_details->name . " unistall process has started =================//");

        logMessage("Removing files of " . $addon_details->name . "process has started.");
        deleteCopiedFiles($delete_file_json_path);

        logMessage("Query execution of unistall the " . $addon_details->name . " addon process has started.");
        $sql_success = execute_sql($uninstall_db);


        $install_sql = false;


        if ($sql_success['status']) {
            updateLicense($product_id);
            $install_sql = true;
            $update_data['uninstall_version'] = $addon_details->current_version;
            $update_data['unistall_by'] = $this->customlib->getStaffID();
            $update_data['addon_ver'] = NULL;
            $update_data['addon_prod'] = NULL;
            $update_data['last_update'] = date('Y-m-d H:i:s');
            $this->db->where('id', $addon_details->id);
            $this->db->update('addons', $update_data);
            $return_data = ['status' => 1, 'msg' => 'Congratulations! The addon uninstalled successfull.'];
        } else {
            $return_data = ['status' => 0, 'msg' => 'Sorry, the addon uninstalled process has failed..'];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($return_data));
    }


    function installNew($addon_folder, $product_id, $addon_details)
    {

        logMessage("*************************************************************************************************************");
        $installation_files = FCPATH . "./addons/" . $addon_folder . "/installer/install/files";
        $install_db =  FCPATH . "./addons/" . $addon_folder . "/installer/install/db/db.sql";

        $back_up_folder = FCPATH . "./addons/" . $addon_folder . "/installer/install/backup/files";
        $backup_json_path =  FCPATH . "./addons/" . $addon_folder . "/installer/install/backup/backup_info.json";


        $uninstall_db =  FCPATH . "./addons/" . $addon_folder . "/installer/uninstall/db.sql";
        $delete_file_json_path =  FCPATH . "./addons/" . $addon_folder . "/installer/uninstall/unistall_directory.json";


        $installer_info_json = read_json(FCPATH . "./addons/" . $addon_folder . "/addon_info.json");

        $check_backup_created = $this->backupDirectory($installation_files, $backup_json_path, $back_up_folder);


        $backup_folder = false;
        $copy_folder = false;
        $install_sql = false;

        logMessage("//================= New Addon " . $addon_details->name . " installing process has started =================//");

        if ($check_backup_created) {
            $backup_folder = true;
            logMessage("//================= New Addon " . $addon_details->name . " file copy process has started =================//");
            $copy_success = copyDirectory($installation_files, FCPATH); // function will replace file and folder during installation

        }


        if ($copy_success) {  //if folder copied successfully 
            $copy_folder = true;
            logMessage("//================= New Addon " . $addon_details->name . " file copy process has ended =================//");
        } else {
            logMessage("File copy issue of " . $addon_details->name . ", new updates files removing process has stated.");
            //===================remove new installed files===================

            deleteCopiedFiles($delete_file_json_path);

            //===================remove new installed files===================
            logMessage("File copy issue of " . $addon_details->name . ", new files removing process has end.");

            // =================

            logMessage("File copy issue of " . $addon_details->name . ", backup restored process has stated.");
            copyDirectory($back_up_folder, FCPATH);  //rollback copy file and folders from backup
            // =================


        }

        if ($copy_folder) { //if folder copied successfully then run install sql

            $sql_success = execute_sql($install_db);  // execute sql installation

            if ($sql_success['status']) {
                $install_sql = true;
            } else {  //rollback installed sql 

                logMessage("Query execution  of " . $addon_details->name . " addon failed, new updates files removing process has stated.");
                //===================remove new installed files===================
                deleteCopiedFiles($delete_file_json_path);
                //===================remove new installed files===================
                logMessage("Query execution of " . $addon_details->name . " addon failed, new files removing process has end.");

                logMessage("Query execution of " . $addon_details->name . " addon failed, backup restored process has stated.");
                copyDirectory($back_up_folder, FCPATH);  //rollback copy file and folders from backup

                logMessage("Query execution of " . $addon_details->name . " addon failed, the uninstall sql process has started.");
                $sql_success = execute_sql($uninstall_db);
            }
        }


        if ($backup_folder && $copy_folder && $install_sql) {
            $update_data['installation_by'] = $this->customlib->getStaffID();
            $update_data['current_version'] = $installer_info_json['version'];
            $update_data['last_update'] = date('Y-m-d H:i:s');
            $this->db->where('product_id', $product_id);
            $this->db->update('addons', $update_data);
            $return_data = ['status' => 1, 'msg' => 'Congratulations! The addon installation was successfully completed.'];
        } else {
            $return_data = ['status' => 0, 'msg' => 'Sorry, the addon installation has failed.'];
        }

        logMessage("//================= New Addon " . $addon_folder . " process has ended =================//");

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($return_data));
    }


    function updateVersion($addon_versions)
    {
        $addon_folder = $addon_versions->directory;
        $addon_name = $addon_versions->name;
        $product_id = $addon_versions->product_id;

        $addon_update_success = false;

        foreach ($addon_versions->nxt_versions as $nxt_version_key => $nxt_version_value) {
            logMessage("*************************************************************************************************************");
            $installation_files = FCPATH . $nxt_version_value->folder_path . "/install/files";
            $install_db =  FCPATH . $nxt_version_value->folder_path . "/install/db/db.sql";

            $back_up_folder = FCPATH . $nxt_version_value->folder_path . "/backup/files";
            $backup_json_path =  FCPATH . $nxt_version_value->folder_path . "/backup/backup_info.json";


            $uninstall_db =  FCPATH . $nxt_version_value->folder_path . "/uninstall/db.sql";
            $delete_file_json_path =  FCPATH . $nxt_version_value->folder_path . "/uninstall/unistall_directory.json";


            $installer_info_json = read_json(FCPATH . "./addons/" . $addon_folder . "/addon_info.json");

            $check_backup_created = $this->backupDirectory($installation_files, $backup_json_path, $back_up_folder);

            $backup_folder = false;
            $copy_folder = false;
            $install_sql = false;
            //=========================

            logMessage("//========Addon " . $addon_name . " version " . $nxt_version_value->version . " update process has started ========//");

            if ($check_backup_created) {
                $backup_folder = true;
                logMessage("//========Addon " . $addon_name . " version " . $nxt_version_value->version . " file copy process has started ========//");
                $copy_success = copyDirectory($installation_files, FCPATH); // function will replace file and folder during installation

            }


            if ($copy_success) {  //if folder copied successfully 
                $copy_folder = true;
                logMessage("//========Addon " . $addon_name . " version " . $nxt_version_value->version . " file copy process has ended ========//");
            } else {
                logMessage("Due to file copy issue, new updates files removing process has stated.");
                //===================remove new installed files===================

                deleteCopiedFiles($delete_file_json_path);

                //===================remove new installed files===================
                logMessage("Due to file copy issue, new files removing process has end.");

                // =================

                logMessage("Due to file copy issue, backup restored process has stated.");
                copyDirectory($back_up_folder, FCPATH);  //rollback copy file and folders from backup
                // =================


            }

            if ($copy_folder) { //if folder copied successfully then run install sql

                $sql_success = execute_sql($install_db);  // execute sql installation

                if ($sql_success['status']) {
                    $install_sql = true;
                } else {  //rollback installed sql 

                    logMessage("Due to query execution failed, new updates files removing process has stated.");
                    //===================remove new installed files===================
                    deleteCopiedFiles($delete_file_json_path);
                    //===================remove new installed files===================
                    logMessage("Due to query execution failed, new files removing process has end.");

                    logMessage("Due to query execution failed, backup restored process has stated.");
                    copyDirectory($back_up_folder, FCPATH);  //rollback copy file and folders from backup

                    logMessage("Due to query execution failed, unistalling the sql executing process has started.");
                    $sql_unistall_success = execute_sql($uninstall_db);
                }
            }


            if ($backup_folder && $copy_folder && $install_sql) {
                $update_data['installation_by'] = $this->customlib->getStaffID();
                $update_data['current_version'] = $nxt_version_value->version;
                $update_data['last_update'] = date('Y-m-d H:i:s');
                $this->db->where('product_id', $product_id);
                $this->db->update('addons', $update_data);
                $addon_update_success = true;
            } else {
                $addon_update_success = false;
                break; // if version update fails break loop;
            }

            logMessage("//=========Addon " . $addon_name . " version " . $nxt_version_value->version . " update process has ended ===========//");
            //=========================



        }


        if ($addon_update_success) {
            $return_data = ['status' => 1, 'msg' => 'Congratulations! Your addon has updated successfully.'];
        } else {

            $return_data = ['status' => 0, 'msg' => 'Sorry, Your addon has updation has failed.'];
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($return_data));
    }




    function backupDirectory($directoryToScan, $backup_json_path, $back_up_folder)
    {
        logMessage("//================= Before install/update backup process has started =================//");

        $backup_json_created = getDirectoryStructure($directoryToScan, $backup_json_path); //create json file insert array for backup of files
        if ($backup_json_created) {

            logMessage("//================= Copying Backup files process has started =================//");

            $back_files = createBackUpFiles($backup_json_path, $back_up_folder);


            if ($back_files) {
                logMessage("//================= Copying Backup files process has ended successfully =================//");
                logMessage("//================= Before install/update Backup process has ended =================//");
                return true;
            } else {
            }
        }
        logMessage("//=================Before install/update Backup process has ended =================//");
        return false;
    }



    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

            $file_type         = $_FILES["file"]['type'];
            $file_size         = $_FILES["file"]["size"];
            $file_name         = $_FILES["file"]["name"];

            $allowed_extension = ["zip"];
            $allowed_mime_type =  ['application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'];
			
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $folder_name               = strtolower(pathinfo(basename($file_name), PATHINFO_FILENAME)); // to retrive the zip file name

            $addon_detail = $this->addons_model->getAddonByFileName($folder_name);
            if (!$addon_detail) {
                $this->form_validation->set_message('handle_upload', 'File is invalid, please upload valid file');
                return false;
            }




            if ($files = filesize($_FILES['file']['tmp_name'])) {



                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }

                if ($file_size > 2097152) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') .
                        number_format(2097152 / 1048576, 2) . ' MB');
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_is_too_small'));
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line('the_file_field_is_required'));
            return false;
        }
    }


    public function getuploaddata()
    {
        $staff_id       = $this->customlib->getStaffID();
        $pag_content    = '';
        $pag_navigation = '';

        if (isset($_POST['data']['page'])) {

            $page = $_POST['data']['page']; /* The page we are currently at */

            $cur_page = $page;
            $page -= 1;
            $per_page     = 12;
            $previous_btn = true;
            $next_btn     = true;
            $first_btn    = true;
            $last_btn     = true;
            $start        = $page * $per_page;

            $where_search = array();

            /* Check if there is a string inputted on the search box */
            if (!empty($_POST['data']['search'])) {
                $where_search['search'] = $_POST['data']['search'];
            }

            /* Retrieve all the posts */
            $contents = $this->addons_model->getlimitwithsearch($per_page, $start, $where_search);

            if (!empty($contents['total_rows'])) {
                foreach ($contents['total_rows'] as $content_key => $content_value) {
                    $this->load->config($content_value->config_name, TRUE, TRUE);
                }
            }


            $data['all_contents'] = $contents['total_rows'];

            $data['selected_content'] = $this->input->post('selected_content');

            $count       = $contents['count'];
            $pag_content = $this->load->view('admin/addons/_getuploaddata', $data, true);

            $no_of_paginations = ceil($count / $per_page);

            if ($cur_page >= 7) {
                $start_loop = $cur_page - 3;
                if ($no_of_paginations > $cur_page + 3) {
                    $end_loop = $cur_page + 3;
                } else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
                    $start_loop = $no_of_paginations - 6;
                    $end_loop   = $no_of_paginations;
                } else {
                    $end_loop = $no_of_paginations;
                }
            } else {
                $start_loop = 1;
                if ($no_of_paginations > 7) {
                    $end_loop = 7;
                } else {
                    $end_loop = $no_of_paginations;
                }
            }
            $pag_navigation .= "<ul class='pagination'>";

            if ($first_btn && $cur_page > 1) {
                $pag_navigation .= "<li p='1' class='page-item unactive'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-left'></i></a></li>";
            } else if ($first_btn) {
                $pag_navigation .= "<li p='1' class='page-item disabled'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-left'></i></a></li>";
            }

            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                $pag_navigation .= "<li p='$pre' class='page-item unactive'><a class='page-link' href='javascript:void(0);'>  " . $this->lang->line('previous') . "</a></li>";
            } else if ($previous_btn) {
                $pag_navigation .= "<li class='page-item disabled'><a class='page-link' href='javascript:void(0);'>" . $this->lang->line('previous') . "</a></li>";
            }
            for ($i = $start_loop; $i <= $end_loop; $i++) {

                if ($cur_page == $i) {
                    $pag_navigation .= "<li p='$i' class = 'page-item active' ><a class='page-link' href='javascript:void(0);'>{$i}</a></li>";
                } else {
                    $pag_navigation .= "<li p='$i' class='page-item unactive'><a class='page-link' href='javascript:void(0);'>{$i}</a></li>";
                }
            }

            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                $pag_navigation .= "<li p='$nex' class='page-item unactive'><a class='page-link' href='javascript:void(0);'>" . $this->lang->line('next') . " </a></li>";
            } else if ($next_btn) {
                $pag_navigation .= "<li class='page-item disabled'><a class='page-link' href='javascript:void(0);'>" . $this->lang->line('next') . "</a></li>";
            }

            if ($last_btn && $cur_page < $no_of_paginations) {
                $pag_navigation .= "<li p='$no_of_paginations' class='page-item unactive'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-right'></i></a></li>";
            } else if ($last_btn) {
                $pag_navigation .= "<li p='$no_of_paginations' class='page-item disabled'><a class='page-link' href='javascript:void(0);'><i class='fa fa-angle-double-right'></i></a></li>";
            }

            $pag_navigation = $pag_navigation . "</ul>";
        }

        $response = array(
            'content'    => $pag_content,
            'navigation' => $pag_navigation,
        );

        echo json_encode($response);
    }
}
