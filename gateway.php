<?php

class Gateway
{
    public $api_key = "";
    public $username = ""; 

    // FIXED: Adjusted to receive Apikey FIRST, and PartnerID SECOND to match your screen layout
    function __construct($api_key, $username)
    {
        $this->api_key = $api_key;
        $this->username = $username;
    }

    function getSendSmsUrl()
    {
        return "https://xxxx/api/services/sendsms/";
    }

    function prepare($data)
    {
        $payload = [
            "apikey"    => $this->api_key,
            "partnerID" => $this->username,
            "mobile"    => $data['numbers'],
            "message"   => $data['message'],
            "shortcode" => $data['sender']
        ];

        $sms_data = json_encode($payload);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getSendSmsUrl());
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $sms_data);
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 512); 
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($sms_data)
        ));

        $response = curl_exec($curl);

        if ($response === false) {
            $err = 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            return $err;
        } else {
            curl_close($curl);
            return $response;
        }
    }

    function send($numbers, $message, $sender)
    {
        $data = [
            "numbers" => $numbers,
            "message" => $message,
            "sender"  => $sender
        ];
        
        return $this->prepare($data);
    }
}
