<?php

namespace WBF\admin;

use WBF\includes\License_Interface;

class License_Manager implements License_Interface{

    function admin_license_menu_item($parent_slug){
        $waboot_license = add_submenu_page( $parent_slug, __( "Waboot License", "wbf" ), __( "License", "wbf" ), "edit_theme_options", "waboot_license", "WBF\admin\License_Manager::license_page" );
    }

    function license_page(){

        if(isset($_POST['submit-license'])){
            try{
                if(isset($_POST['license_code'])){
                    if(isset( $_POST['license_nonce_field'] ) && wp_verify_nonce($_POST['license_nonce_field'],'submit_licence_nonce') ){
                        $license = self::sanitize_license($_POST['license_code']);
                        if($license){
                            update_option("waboot_license",$license);
                            ?>
                            <div class="updated">
                                <p><?php _e( 'License Updated!', "wbf" ); ?></p>
                            </div>
                        <?php
                        }else{
                            throw new LicenseException(_( 'Unable to update the license!', "wbf" ));
                        }
                    }
                }
            }catch(LicenseException $e){
                ?>
                <div class="updated">
                    <p><?php echo $e->getMessage(); ?></p>
                </div>
                <?php
            }
        }

        $current_license = get_option("waboot_license","");
        $status = self::get_license_status();

        ?>
        <div class="wrap">
            <h2><?php _e( "Waboot License", "wbf" ); ?></h2>
            <p>
            <form method="post" action="admin.php?page=waboot_license" >
                <p><?php _e("Here you can enter your license:", "wbf"); ?></p>
                <input type="text" value="<?php echo $current_license; ?>" name="license_code" />
                <p class="submit">
                    <input type="submit" name="submit-license" id="submit" class="button button-primary" value="Validate License">
                </p>
                <div id="license-status">
                    <p>Current License Status: <?php self::print_license_status($status); ?></p>
                </div>
                <?php wp_nonce_field('submit_licence_nonce','license_nonce_field'); ?>
            </form>
            </p>
	        <?php \WBF::print_copyright(); ?>
        </div>
    <?php
    }

    private function print_license_status($status){
        switch ($status) {
            case "Active":
                echo "<span class='license-active'>$status</span>";
                break;
            case "Invalid":
                echo "<span class='license-invalid'>$status</span>";
                break;
            case "Expired":
                echo "<span class='license-expired'>$status</span>";
                break;
            case "Suspended":
                echo "<span class='license-suspended'>$status</span>";
                break;
	        case "no-license":
		        echo "<span class='license-suspended'>".__("No license provided","wbf")."</span>";
		        break;
            default:
                echo "<span class='license-unk'>Unknown status</span>";
                break;
        }
    }

    static function get_license_status(){
        $license = get_option("waboot_license","");
        if($license != ""){
            $localkey = get_option("waboot_license_localkey",false);
            if(!$localkey){
                $results = self::check_license($license);
            }else{
                $results = self::check_license($license,$localkey);
                if(isset($results['localkey'])){
                    $localkeydata = $results['localkey'];
                    update_option("waboot_license_localkey",$localkeydata);
                }
            }
            return $results['status'];
        }else{
            return "no-license";
        }
    }

    public function sanitize_license($license){
        return $license;
    }

    public function check_license($licensekey, $localkey='') {

        // -----------------------------------
        //  -- Configuration Values --
        // -----------------------------------

        // Enter the url to your WHMCS installation here
        $whmcsurl = 'https://waga.it/billing/';
        // Must match what is specified in the MD5 Hash Verification field
        // of the licensing product that will be used with this check.
        $licensing_secret_key = 'wbUz9TP5d7oa89F';
        // The number of days to wait between performing remote license checks
        $localkeydays = 15;
        // The number of days to allow failover for after local key expiry
        $allowcheckfaildays = 5;

        // -----------------------------------
        //  -- Do not edit below this line --
        // -----------------------------------

        $check_token = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
        $checkdate = date("Ymd");
        $domain = $_SERVER['SERVER_NAME'];
        $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
        $dirpath = dirname(__FILE__);
        $verifyfilepath = 'modules/servers/licensing/verify.php';
        $localkeyvalid = false;
        if ($localkey) {
            $localkey = str_replace("\n", '', $localkey); # Remove the line breaks
            $localdata = substr($localkey, 0, strlen($localkey) - 32); # Extract License Data
            $md5hash = substr($localkey, strlen($localkey) - 32); # Extract MD5 Hash
            if ($md5hash == md5($localdata . $licensing_secret_key)) {
                $localdata = strrev($localdata); # Reverse the string
                $md5hash = substr($localdata, 0, 32); # Extract MD5 Hash
                $localdata = substr($localdata, 32); # Extract License Data
                $localdata = base64_decode($localdata);
                $localkeyresults = unserialize($localdata);
                $originalcheckdate = $localkeyresults['checkdate'];
                if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                    $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                    if ($originalcheckdate > $localexpiry) {
                        $localkeyvalid = true;
                        $results = $localkeyresults;
                        $validdomains = explode(',', $results['validdomain']);
                        if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                        $validips = explode(',', $results['validip']);
                        if (!in_array($usersip, $validips)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                        $validdirs = explode(',', $results['validdirectory']);
                        if (!in_array($dirpath, $validdirs)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                    }
                }
            }
        }
        if (!$localkeyvalid) {
            $postfields = array(
                'licensekey' => $licensekey,
                'domain' => $domain,
                'ip' => $usersip,
                'dir' => $dirpath,
            );
            if ($check_token) $postfields['check_token'] = $check_token;
            $query_string = '';
            foreach ($postfields AS $k=>$v) {
                $query_string .= $k.'='.urlencode($v).'&';
            }
            if (function_exists('curl_exec')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $data = curl_exec($ch);
                curl_close($ch);
            } else {
                $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
                if ($fp) {
                    $newlinefeed = "\r\n";
                    $header = "POST ".$whmcsurl . $verifyfilepath . " HTTP/1.0" . $newlinefeed;
                    $header .= "Host: ".$whmcsurl . $newlinefeed;
                    $header .= "Content-type: application/x-www-form-urlencoded" . $newlinefeed;
                    $header .= "Content-length: ".@strlen($query_string) . $newlinefeed;
                    $header .= "Connection: close" . $newlinefeed . $newlinefeed;
                    $header .= $query_string;
                    $data = '';
                    @stream_set_timeout($fp, 20);
                    @fputs($fp, $header);
                    $status = @socket_get_status($fp);
                    while (!@feof($fp)&&$status) {
                        $data .= @fgets($fp, 1024);
                        $status = @socket_get_status($fp);
                    }
                    @fclose ($fp);
                }
            }
            if (!$data) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
                if ( isset($originalcheckdate) && $originalcheckdate > $localexpiry) {
                    $results = $localkeyresults;
                } else {
                    $results = array();
                    $results['status'] = "Invalid";
                    $results['description'] = "Remote Check Failed";
                    return $results;
                }
            } else {
                preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
                $results = array();
                foreach ($matches[1] AS $k=>$v) {
                    $results[$v] = $matches[2][$k];
                }
            }
            if (!is_array($results)) {
                die("Invalid License Server Response");
            }
            if (isset($results['md5hash']) && $results['md5hash']) {
                if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
                    $results['status'] = "Invalid";
                    $results['description'] = "MD5 Checksum Verification Failed";
                    return $results;
                }
            }
            if ($results['status'] == "Active") {
                $results['checkdate'] = $checkdate;
                $data_encoded = serialize($results);
                $data_encoded = base64_encode($data_encoded);
                $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
                $data_encoded = strrev($data_encoded);
                $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
                $data_encoded = wordwrap($data_encoded, 80, "\n", true);
                $results['localkey'] = $data_encoded;
            }
            $results['remotecheck'] = true;
        }
        unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
        return $results;
    }
}

class LicenseException extends \Exception{

}