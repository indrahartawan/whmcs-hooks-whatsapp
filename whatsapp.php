<?php

/**
 * WhatsApp notification hook
 *
 * @package    WhatsApp
 * @author     Indra Hartawan <emailme@indrahartawan.com>
 * @copyright  Copyright (c) Indra Hartawan
 * @license    MIT License
 * @version    $Id$
 * @link       https://github.com/indrahartawan/whmcs-hook-whatsapp
 */


if (!defined("WHMCS"))
    die("This file cannot be accessed directly");


function whatsapp_log($log_message)
{
   $today = date("M d, Y H:i:s");
   $txt = $today . " : " . $log_message ;
   $writelog = file_put_contents(dirname(__FILE__) ."/whatsapp.log", $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
}

function get_wa_config($configfile)
{
    $json = file_get_contents(dirname(__FILE__) ."/". $configfile);
    $config = json_decode($json, true);
    if ($config["debug"]) {
       whatsapp_log("Get configuration.");
    }
    return $config;
}

function get_client_details($clientid)
{
    //get configuration veriables
    $configvars = get_wa_config("whatsapp.json");
    $client = array();
    $command = "getclientsdetails";
    $adminuser = $configvars["adminuser"];
    $values["clientid"] = $clientid;
    $values["stats"] = false;
    $results = localAPI($command, $values, $adminuser);
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $results, $values, $tags);
    xml_parser_free($parser);
    $client = $results;
    $client["key"] = $configvars["whatsapp_api_key"];
    $client["userkey"] = $configvars["whatsapp_api_userkey"];
    $client["passkey"] = $configvars["whatsapp_api_passkey"];
    $client["api_url"] = $configvars["whatsapp_api_url"];
    $client["debug"] = $configvars["debug"];
    if ($client["debug"]) {
       whatsapp_log("Get client details");
    }
    return $client;
}

function sent_whatsapp($phone_no,$passkey,$message,$api_url,$userkey,$debug) {
        $domain = gethostname();
        $data = array(
        'userkey' => $userkey,
        'passkey' => $passkey,
        'nohp' => $phone_no,
        'pesan' => $message);
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_TIMEOUT,30);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        'userkey' => $userkey,
        'passkey' => $passkey,
        'nohp' => $phone_no,
        'pesan' => $message));
        $result = json_decode(curl_exec($ch), true);
        $err = json_decode(curl_error($ch), true);
        curl_close ($ch);
        if ($err) {
           whatsapp_log("cURL Error : " . $err);
        }
        return $result;
}

function whatsapp_new_order($vars) {
   if (isset($_SESSION['uid']) && intval($_SESSION['uid'])!==0){
   // Checking User Logged In
      $clientdetails = get_client_details($_SESSION['uid']);
      
      if ($clientdetails['result'] == "success" ) {
      
      date_default_timezone_set("Asia/Jakarta");
      $today = date("M d, Y H:i:s");
      $firstname = $clientdetails['firstname'];
      $yr_phone_no = $clientdetails['phonenumber'];
      // replace error phone number format
      //$yr_phone_no = ereg_replace ("^8","08", $yr_phone_no); #deprecated on PHP 7.x
      $yr_phone_no = preg_replace ("/^8/","08", $yr_phone_no);
      //$phone_no = "08XXXXXXXXXX";
      $phone_no = $yr_phone_no;
      $orderid = $vars['OrderID'];
      $ordernumber = $vars['OrderNumber'];
      $invoiceid = $vars['InvoiceID'];
      $amount = "Rp. ". number_format($vars['TotalDue'],2);

      //define payment method based on the database to make it friendly. Add yourself based on the value in the 'PaymentMethod'
      switch($vars['PaymentMethod']) {
          case "va_mandiri":
               $paymentmethod = "VA Bank Mandiri";
               break;
          default:
               $paymentmethod = $vars['PaymentMethod'];
               break;
      }

       $message = "Hi ". $firstname . ", 

Ini adalah pesan otomatis. Terima kasih telah melakukan pemesanan di Exabytes Indonesia pada ". $today .".

*Berikut detail pemesanan Anda :*
Nomor pemesanan : ". $ordernumber ."
Metode pembayaran : ". $paymentmethod ."
Total Pembelian : ". $amount ."
Lihat Invoice : https://billing.exabytes.co.id/viewinvoice.php?id=". $invoiceid ."
(harap login ke client area untuk melihat invoice dan melakukan pembayaran)

Pertanyaan soal pembayaran di Exabytes:
https://www.exabytes.co.id/pembayaran

Promo terbaik Exabytes:
https://www.exabytes.co.id/promo

Jika mengalami kendala bisa menghubungi kami via chat di nomor official berikut 082215468046.

_Silahkan abaikan pesan ini jika pembayaran Anda telah diselesaikan._ 
Terima kasih.";

        $phone_no = preg_replace( "/(\n)/", ",", $phone_no );
        $phone_no = preg_replace( "/(\r)/", "", $phone_no );
        $api_url = $clientdetails['api_url'];
        $userkey = $clientdetails['userkey'];
        $passkey = $clientdetails['passkey'];
        if ($clientdetails['debug']){
           $msg = "phone_no -> " . $phone_no . " | userkey -> " . $userkey . " | passkey -> " . $passkey . " | api_url -> " . $api_url;
           whatsapp_log($msg);
        }
        $result = sent_whatsapp($phone_no,$passkey,$message,$api_url,$userkey,$clientdetails['debug']);
        if ($clientdetails['debug']){ whatsapp_log("Message successfully sent to " . $phone_no . ""); }
     }
  }

}

add_hook("AfterShoppingCartCheckout",999,"whatsapp_new_order");

?>
