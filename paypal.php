<?php


/**
 *
 */

require_once dirname(__FILE__) . '/config.php';

class Paypal
{
    const GET_TOKEN_URL = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    private $accessToken;

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
            $this->accessToken = $json->access_token;
            var_dump($this->accessToken);
            return $json->access_token;
        }

        curl_close($ch);

    }

    /**
     *
     */
    public function getPaymentId($amount, $currency, $description)
    {
        $this->getAccessToken();
        $descriptionPayment = array(
            "intent" => "sale",
            "payer" => array(
                "payment_method" => "paypal"
            ),
            "transactions" => array(
                array(
                    "amount" => array(
                        "total" => $amount,
                        "currency" => $currency
                    ),
                    "description" => $description
                )
            ),
            "redirect_urls" => array(
                "return_url" => RETURN_URL,
                "cancel_url" => CANCEL_URL
            )
        );

        $descriptionPaymentPost =  json_encode($descriptionPayment, JSON_NUMERIC_CHECK);
        var_dump($descriptionPaymentPost);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/payments/payment");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $descriptionPaymentPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->accessToken
        ));

        $result = curl_exec($ch);
        if (empty($result)) {
            die("Error: No response.");
        } else {
            $json = json_decode($result);
            var_dump($json);
        }
    }

    public function confirmPayment()
    {

    }
}

$paypal = new Paypal();
$paypal->getPaymentId(45, 'USD', 'testpayment');
