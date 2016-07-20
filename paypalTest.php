<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 20/07/2016
 * Time: 11:01
 */

require_once("paypal.php");
$paypal = new Paypal();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "cancel":
            echo "Operation canceled";
            break;
        case "return":
            $token = $_GET['token'];
            $paymentId = $_GET['paymentId'];
            $PayerID = $_GET['PayerID'];

            var_dump($paypal->confirmPayment($paymentId, $PayerID));
            var_dump("OPERATION INFO");
            var_dump($paypal->getPaymentInfo($paymentId));
            break;
        case "info":
            $token = $_GET['token'];
            $paymentId = $_GET['paymentId'];
            $PayerID = $_GET['PayerID'];

            var_dump($paypal->getPaymentInfo($paymentId));
    }
} else {
    $paypal->getAccessToken();
    $url = $paypal->createPayment(1500, 'EUR', 'Achat de tongs & maillots de bain');
    echo $url;
    header('Location:' . $url);
}