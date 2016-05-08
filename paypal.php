<?php

require_once dirname(__FILE__) . '/config.php';

/**
 * Paypal class
 */
class Paypal
{
    const GET_TOKEN_URL      = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    const CREATE_PAYMENT_URL = 'https://api.sandbox.paypal.com/v1/payments/payment';
    const DEFAULT_PAYMENT    = 'paypal';

    /**
     * accessToken
     * @var string
     */
    private $accessToken;


    /**
     * Class constructor
     */
    function __construct()
    {
        $this->prepare(CLIENT_ID, CLIENT_SECRET, RETURN_URL, CANCEL_URL);
        $this->getAccessToken();
    }

    /**
     * get an access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::GET_TOKEN_URL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, CLIENT_ID.":".CLIENT_SECRET);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);

        if(curl_error($ch)) {
            throw new Exception("Error Processing Request: ". curl_error($ch), 1);
        }

        $response = json_decode($result);
        $this->accessToken = $response->access_token;
        curl_close($ch);
        return $this->accessToken;
    }

    /**
     * create a paypal payment
     *
     * @param float $amount
     * @param string $currency
     * @param string $description
     *
     * @return string redirect url
     */
    public function createPayment($amount, $currency, $description)
    {
        $this->prepareParams($amount, $currency, $description);

        $descriptionPayment = array(
            "intent" => "sale",
            "payer" => array(
                "payment_method" => self::DEFAULT_PAYMENT
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

        $jsonDescriptionPayment = json_encode($descriptionPayment, JSON_NUMERIC_CHECK);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::CREATE_PAYMENT_URL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDescriptionPayment);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->accessToken
        ));

        $result = curl_exec($ch);

        if(curl_error($ch)) {
            throw new Exception("Error Processing Request: ". curl_error($ch), 1);
        }

        $response = json_decode($result, true);
        $key = array_search("REDIRECT", array_column($response["links"], 'method'));
        curl_close($ch);
        return $response["links"][$key]["href"];
    }

    /**
     * confirm the payment
     *
     * @param string payerId passed in the return url by paypal
     *
     * @return array paiement description
     */
    public function confirmPayment($paymentId, $payerId)
    {

        $jsonPayerId = json_encode(array("payer_id" => $payerId));
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::CREATE_PAYMENT_URL . '/' . $paymentId . '/execute');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayerId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->accessToken
        ));

        $result = curl_exec($ch);

        if(curl_error($ch)) {
            throw new Exception("Error Processing Request: ". curl_error($ch), 1);
        }
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * check given config format
     *
     * @param string $clientId
     * @param clientSecret
     * @param $backUrl
     * @param cancelUrl
     *
     * @return boolean
     */
    private function prepare($clientId, $clientSecret, $backUrl, $cancelUrl)
    {
        if ('' == $clientId) {
            throw new Exception("Required CLIENT_ID is missing", 1);
        }
        if ('' == $clientSecret) {
            throw new Exception("Required CLIENT_SECRET is missing", 1);
        }
        if ('' == $backUrl) {
            throw new Exception("Required RETURN_URL is missing", 1);
        }
        if ('' == $cancelUrl) {
            throw new Exception("Required CANCEL_URL is missing", 1);
        }
        return true;
    }

    /**
     * check given params format
     *
     * @param  float $amount
     * @param  string $currency
     * @param  string $description
     *
     * @return boolean
     */
    public function prepareParams($amount, $currency, $description)
    {
        if ('' == $amount) {
            throw new Exception("Required Amount parameter is missing", 1);
        } elseif (!is_numeric($amount)) {
            throw new Exception("Numeric value expected for amount param, " . gettype($amount) . " found", 1);
        }
        if ('' == $currency) {
            throw new Exception("Required Currency param is missing", 1);
        } elseif (!preg_match("#[A-Z]{3}#", $currency)) {
            throw new Exception("Currency param must be a 3-letter currency_code", 1);
        }
        if ('' == $description) {
            throw new Exception("Required description param is missing", 1);
        } elseif (!is_string($description)) {
            throw new Exception("String value expected for description param, " . gettype($description) . " found", 1);
        } elseif (strlen($description) > 127) {
            throw new Exception("127 characters max. for description param", 1);
        }
        return true;
    }
}
//example
try {
    $paypal = new Paypal();
    $paypal->getAccessToken();
    var_dump($paypal->createPayment(45, 'USD', 'test payment'));
    // sample data : paymentId=PAY-9DV39250TB438363BK4XI3ZY & PayerID=45J8NVHQ93A9A
    var_dump($paypal->confirmPayment('PAY-9DV39250TB438363BK4XI3ZY', '45J8NVHQ93A9A'));
} catch (Exception $e) {
    echo 'Error: ' . $e;
}
