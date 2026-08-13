<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class License_service
{
    private $CI;
    private $secretKey;
    private $hmacKey   = 'MY_SUPER_SECRET';
    private $cipher    = 'AES-256-CBC';
    private $ivSeed    = 'hospital_license_iv';
    private $licenseFile;

	private $verifyInterval = '+12 hours';
    private $graceWindow    = '+3 days';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->licenseFile = APPPATH . 'cache/.system.dat';
        $this->secretKey   = hex2bin('1c34d93c511ea244042191c48ca7c149af02e968610da93624355b475395416c');

    }

    public function saveLicense(array $shlk)
    {
        $payload = $this->loadPayload();
        if (!is_array($payload)) {
            $payload = array('SHLK' => array(), 'addons' => array());
        }
        if (!isset($payload['addons']) || !is_array($payload['addons'])) {
            $payload['addons'] = array();
        }

        $payload['SHLK'] = $this->stampBlock($shlk);

        return $this->writePayload($payload);
    }

    public function loadLicense()
    {
        $payload = $this->loadPayload();

    
        if (!is_array($payload) || empty($payload['SHLK']) || !is_array($payload['SHLK'])) {
            return null;
        }
        return $payload['SHLK'];
    }

    public function clearLicense()
    {
        if (is_file($this->licenseFile)) {
            @unlink($this->licenseFile);
        }
    }

    public function saveAddonLicense($productId, array $block)
    {
        $productId = (string)$productId;
        if ($productId === '') {
            return false;
        }

        $payload = $this->loadPayload();
        if (!is_array($payload)) {
            $payload = array('SHLK' => array(), 'addons' => array());
        }
        if (!isset($payload['addons']) || !is_array($payload['addons'])) {
            $payload['addons'] = array();
        }

        $payload['addons'][$productId] = $this->stampBlock($block);

        return $this->writePayload($payload);
    }

    public function loadAddonLicense($productId)
    {
        $productId = (string)$productId;
        $payload = $this->loadPayload();
        if (!is_array($payload) || empty($payload['addons']) || !is_array($payload['addons'])) {
            return null;
        }
        if (!isset($payload['addons'][$productId]) || !is_array($payload['addons'][$productId])) {
            return null;
        }
        return $payload['addons'][$productId];
    }

    public function clearAddonLicense($productId)
    {
        $productId = (string)$productId;
        $payload = $this->loadPayload();
        if (!is_array($payload) || empty($payload['addons']) || !is_array($payload['addons'])) {
            return true;
        }
        if (!isset($payload['addons'][$productId])) {
            return true;
        }
        unset($payload['addons'][$productId]);
        return $this->writePayload($payload);
    }

    public function listAddons()
    {
        $payload = $this->loadPayload();
        if (!is_array($payload) || empty($payload['addons']) || !is_array($payload['addons'])) {
            return array();
        }
        return $payload['addons'];
    }

    public function saveAndroidLicense(array $block)
    {
        $payload = $this->loadPayload();
        if (!is_array($payload)) {
            $payload = array('SHLK' => array(), 'addons' => array());
        }

        $payload['android'] = $this->stampBlock($block);

        return $this->writePayload($payload);
    }

    public function loadAndroidLicense()
    {
        $payload = $this->loadPayload();
        if (!is_array($payload) || empty($payload['android']) || !is_array($payload['android'])) {
            return null;
        }
        return $payload['android'];
    }

    public function clearAndroidLicense()
    {
        $payload = $this->loadPayload();
        if (!is_array($payload) || !isset($payload['android'])) {
            return true;
        }
        unset($payload['android']);
        return $this->writePayload($payload);
    }

    public function needsVerify($productId = null)
    {
        $block = ($productId === null) ? $this->loadLicense() : $this->loadAddonLicense($productId);
        return $this->blockNeedsVerify($block);
    }

    public function needsAndroidVerify()
    {
        return $this->blockNeedsVerify($this->loadAndroidLicense());
    }

    public function inGracePeriod($productId = null)
    {
        $block = ($productId === null) ? $this->loadLicense() : $this->loadAddonLicense($productId);
        return $this->blockInGrace($block);
    }

    public function androidInGracePeriod()
    {
        return $this->blockInGrace($this->loadAndroidLicense());
    }

    /**
     * Canonical domain form used both when binding a license and when checking it.
     * Must be applied identically on store + compare, otherwise a legitimate
     * install could be falsely deregistered.
     */
    public function normalizeDomain($raw)
    {
        $d = strtolower(trim((string)$raw));
        $d = preg_replace('#^https?://#i', '', $d); // strip scheme
        $d = preg_replace('#/.*$#', '', $d);         // strip path / trailing slash
        $d = preg_replace('#^www\.#i', '', $d);      // strip leading www.
        $d = preg_replace('#:(80|443)$#', '', $d);   // strip default ports
        return $d;
    }

    /**
     * The domain the app is actually being served from right now.
     * Prefers the real request host so a copied install on a new domain is
     * detected even when config.php has a hard-coded base_url. Falls back to
     * base_url() for CLI / cron contexts where HTTP_HOST is absent.
     */
    public function currentDomain()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $this->normalizeDomain($_SERVER['HTTP_HOST']);
        }
        return $this->normalizeDomain(base_url());
    }

    /**
     * True when the stored block's bound domain matches the current domain.
     * An empty/missing domain (legacy file) is treated as "not yet bound" and
     * returns true so existing installs are not deregistered on upgrade; the
     * domain is backfilled on the next save by stampBlock().
     */
    public function domainMatches($block)
    {
        if (!is_array($block) || empty($block['domain'])) {
            return true;
        }
        return $this->normalizeDomain($block['domain']) === $this->currentDomain();
    }

    private function blockNeedsVerify($block)
    {
        if (!is_array($block)) {
            return true;
        }
        if (empty($block['lic_status']) || $block['lic_status'] !== 'ACTIVE') {
            return true;
        }
        if (empty($block['next_check'])) {
            return true;
        }
        return time() >= (int)$block['next_check'];
    }

    private function blockInGrace($block)
    {
        if (!is_array($block) || empty($block['lic_last_verified'])) {
            return false;
        }
        $cutoff = strtotime('-' . ltrim($this->graceWindow, '+'), time());
        return (int)$block['lic_last_verified'] >= $cutoff;
    }

    private function stampBlock(array $block)
    {
        $now = time();
        if (empty($block['lic_status'])) {
            $block['lic_status'] = 'ACTIVE';
        }
        // Bind the registration domain once, then preserve it on every later
        // save so a copied install can never silently re-bind to a new domain.
        if (empty($block['domain'])) {
            $block['domain'] = $this->currentDomain();
        }
        $block['lic_last_verified'] = $now;
        $block['next_check']        = strtotime($this->verifyInterval, $now);
        return $block;
    }

    private function loadPayload()
    {
        if (!is_file($this->licenseFile)) {
            return null;
        }

        $encrypted = @file_get_contents($this->licenseFile);
        if ($encrypted === false || $encrypted === '') {
            return null;
        }

        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->secretKey, 0, $this->iv());
        if ($decrypted === false) {
            return null;
        }

        $envelope = json_decode($decrypted, true);
        if (!is_array($envelope) || !isset($envelope['payload'], $envelope['checksum'])) {
            return null;
        }

        $expected = hash_hmac('sha256', json_encode($envelope['payload']), $this->hmacKey);
        if (!hash_equals($expected, $envelope['checksum'])) {
            return null;
        }

        $payload = $envelope['payload'];
        if (!is_array($payload)) {
            return null;
        }

        return $this->migratePayload($payload);
    }

    private function migratePayload(array $payload)
    {
        if (isset($payload['SHLK']) && is_array($payload['SHLK'])) {
            $shlk = $payload['SHLK'];
            if (!isset($shlk['productCode']) || $shlk['productCode'] === '') {
                $shlk['productCode'] = 'SMS101';
            }
            if (!isset($shlk['lic_status']) && isset($payload['lic_status'])) {
                $shlk['lic_status'] = $payload['lic_status'];
            }
            if (!isset($shlk['lic_last_verified']) && isset($payload['lic_last_verified'])) {
                $ts = is_numeric($payload['lic_last_verified'])
                    ? (int)$payload['lic_last_verified']
                    : strtotime($payload['lic_last_verified']);
                if ($ts) {
                    $shlk['lic_last_verified'] = $ts;
                }
            }
            if (!isset($shlk['next_check']) && !empty($shlk['lic_last_verified'])) {
                $shlk['next_check'] = strtotime($this->verifyInterval, (int)$shlk['lic_last_verified']);
            }
            $payload['SHLK'] = $shlk;
        }

        if (!isset($payload['addons']) || !is_array($payload['addons'])) {
            $payload['addons'] = array();
        }

        unset($payload['lic_status'], $payload['lic_last_verified']);
        return $payload;
    }

    private function writePayload(array $payload)
    {

        
        $dir = dirname($this->licenseFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (!isset($payload['addons']) || !is_array($payload['addons'])) {
            $payload['addons'] = array();
        }

        $envelope = array('payload' => $payload);
        $envelope['checksum'] = hash_hmac('sha256', json_encode($payload), $this->hmacKey);

        $encrypted = openssl_encrypt(json_encode($envelope), $this->cipher, $this->secretKey, 0, $this->iv());
        if ($encrypted === false) {
            return false;
        }

        return @file_put_contents($this->licenseFile, $encrypted) !== false;
    }

    private function iv()
    {
        return substr(hash('sha256', $this->ivSeed), 0, 16);
    }
}
