<?php
// Ensure the sender function is only declared once globally to prevent fatal crashes
if (!function_exists('Sende')) {
    function sender($recipients, $message) {
        // Force the use of the correct gateway filename from your screenshot
        if (file_exists('Gateway.php')) {
            require_once('Gateway.php');
        }

      
        $PartnerID = "xxxxx"; 
        $Apikey    = "xxxxxxxxxxxxxxxxxxxx"; 
        $Shortcode = "xxxxxxxxx"; 
    

        try {
            // This matches the exact class name and variable order from your screen
            $gateway = new TextSMS_Gateway($Apikey, $PartnerID);
            
            // Execute and get the response back from the gateway
            $response = $gateway->send($recipients, $message, $Shortcode);
            
            $debug_response = is_array($response) || is_object($response) ? json_encode($response) : $response;
            echo "<script>alert('Target: " . $recipients . "\\nGateway Response: " . addslashes($debug_response) . "');</script>";
            
        } catch (Exception $e) {
            error_log("SMS Gateway Error: " . $e->getMessage());
            echo "<script>alert('Gateway Exception: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Scoped variables to completely protect add_payloan.php core memory spaces
$sms_val = isset($idno) ? $idno : '';
$sms_amount = isset($_POST["amount"]) ? intval($_POST["amount"]) : 0;
$sms_newloan = isset($newloan) ? $newloan : 0;
$sms_loantype = isset($loantype) ? $loantype : 'Loan';

// Auto-detect whichever database handle is actively open in the parent script
$db_conn = null;
if (isset($db)) {
    $db_conn = $db;
} elseif (isset($con)) {
    $db_conn = $con;
}

if (!empty($sms_val) && $db_conn) {
    if (is_object($db_conn) && method_exists($db_conn, 'escape_string')) {
        $escaped_val = $db_conn->escape_string($sms_val);
    } elseif (is_object($db_conn) && isset($db_conn->connection)) {
        $escaped_val = mysqli_real_escape_string($db_conn->connection, $sms_val);
    } else {
        $escaped_val = preg_replace('/[^0-9]/', '', $sms_val);
    }

    $sms_sql = "SELECT fname, phoneno FROM details WHERE idno = '$escaped_val'";
    
    if (method_exists($db_conn, 'query')) {
        $sms_result = $db_conn->query($sms_sql);
    } else {
        $sms_result = mysqli_query($db_conn, $sms_sql);
    }

    if ($sms_result) {
        if (is_object($sms_result) && method_exists($sms_result, 'fetch_assoc')) {
            while ($sms_row = $sms_result->fetch_assoc()) {
                $raw_phone = trim($sms_row["phoneno"]);
              
                
                
                // Perfect phone formatter for numbers stored like 720958327
                if (substr($raw_phone, 0, 3) === '254') {
                    $recipients = $raw_phone;
                } elseif (substr($raw_phone, 0, 1) === '0') {
                    $recipients = '254' . substr($raw_phone, 1);
                } elseif (substr($raw_phone, 0, 1) === '7' || substr($raw_phone, 0, 1) === '1') {
                    $recipients = '254' . $raw_phone;
                } else {
                    $recipients = $raw_phone;
                }

                $message = "Hello " . $sms_row['fname'] . " you have paid a loan of Amount Kshs. " . $sms_amount . " For " . $sms_loantype . " Loan... Your New Loan Balance is Kshs." . $sms_newloan;
                sender($recipients, $message);
            }
        } else {
            $fetch_function = function_exists('mysqli_fetch_assoc') ? 'mysqli_fetch_assoc' : 'mysqli_fetch_array';
            while ($sms_row = $fetch_function($sms_result)) {
                $raw_phone = trim($sms_row["phoneno"]);
              
                
                
                if (substr($raw_phone, 0, 3) === '254') {
                    $recipients = $raw_phone;
                } elseif (substr($raw_phone, 0, 1) === '0') {
                    $recipients = '254' . substr($raw_phone, 1);
                } elseif (substr($raw_phone, 0, 1) === '7' || substr($raw_phone, 0, 1) === '1') {
                    $recipients = '254' . $raw_phone;
                } else {
                    $recipients = $raw_phone;
                }

                $message = "Hello " . $sms_row['fname'] . " you have paid a loan of Amount Kshs. " . $sms_amount . " For " . $sms_loantype . " Loan... Your New Loan Balance is Kshs." . $sms_newloan;
                sender($recipients, $message);
            }
        }
    }
}
?>
