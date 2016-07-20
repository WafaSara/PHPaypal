#MODULE PHPayPal
#Module léger d'achat PayPal

##Informations
PHPaypal est un client léger pour passer des commandes de type Express Checkout avec PayPal
Très facile à installer et à configurer, il répondra parfairement aux besoin de petites plates-formes

##Instalation
Déposez simplements la classe Paypal et le fichier de configuration (config.php.dist) dans votre projet

##Configuration
Renommez le fichier config.php.dist en config.php et renseignez les informations.

* CLIENT_ID 
    Longue chaine de caractères délivrée par PayPal permettant de vous identifier
* CLIENT_SECRET
    Longue chaine de caractères délivrée par PayPal permettant de vous identifier
* RETURN_URL
    L'URL vers laquelle PayPal va rediriger l'acheteur après avoir validé le paiement. Doit pointer sur votre site
* CANCEL_URL
    L'URL vers laquelle PayPal va rediriger l'acheteur s'il décide d'annuler la transaction

##Utilisation
Comment utiliser notre module.
1. Instanciez un objet de la classe
`$phpaypal = new PHPayl()`
2. Générez un token auprès de PayPal
`$paypal->getAccessToken();`
3. Créez un paiement
`$url = $paypal->createPayment(62, 'EUR', 'Paire de chaussures');`
4. Redirigez l'utilisateur vers l'URL renvoyée par la fonctionne précédente
5. L'utilisateur est alors entre les mains de PayPal. Il sera redirigé vers votre site via les URL définies dans la configuration
6. Si l'utilisateur à payé, vous pouvez confirmez le paiement avec 
`$paypal->confirmPayment($paymentId, $PayerID);`
Les arguments sont donnés par PayPal via l'URL
7. Si le paiement est annulé, nous ne pouvons plus vous aider
8. Vous pouvez avoir des informations sur le statut du paiment avec
`$paypal->getPaymentInfo($paymentId)`

#A propos
ESGI 2016 4A EB3. 
* Antoine Cusset
* Jolan Levy
* Wafae Ben Sahla
* Alexandre Morin
* Hugo Piso