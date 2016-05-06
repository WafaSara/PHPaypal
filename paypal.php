<?php

require_once 'config.php';

class Paypal
{

    function __construct()
    {

    }

    public function getAccessToken()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, CLIENT_ID.":".CLIENT_SECRET);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);

        if (empty($result)) {
            die("Error: No response.");
        } else {
            $json = json_decode($result);
            return $json->access_token;
        }

        curl_close($ch);

    }

    public function getPaymentId()
    {

    }

    public function confirmPayment()
    {
        
    }
}


