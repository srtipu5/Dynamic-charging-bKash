<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use URL;

class BkashDynamicChargeController extends Controller
{
    private $base_url;
    private $username;
    private $password;
    private $app_key;
    private $app_secret;

    public function __construct()
    {
        $this->base_url = 'https://sbdynamic.pay.bka.sh/v1';
        $this->username = env('BKASH_USERNAME');
        $this->password = env('BKASH_PASSWORD');
        $this->app_key = env('BKASH_APP_KEY');
        $this->app_secret = env('BKASH_APP_SECRET');
    }
    public function authHeaders()
    {
        return array(
            'Content-Type:application/json',
            'Authorization:' . $this->grant(),
            'X-APP-Key:' . $this->app_key
        );
    }

    public function curlWithBody($url, $header, $method, $body_data)
    {
        $curl = curl_init($this->base_url . $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body_data);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function grant()
    {
        $header = array(
            'Content-Type:application/json',
            'username:' . $this->username,
            'password:' . $this->password
        );

        $body_data = array('app_key' => $this->app_key, 'app_secret' => $this->app_secret);

        $response = $this->curlWithBody('/auth/grant-token', $header, 'POST', json_encode($body_data));

        $token = json_decode($response)->id_token;

        return $token;
    }

    public function payment(Request $request)
    {
        return view('bkash.pay');
    }

    public function createPayment(Request $request)
    {
        if (!$request->amount || $request->amount < 1) {
            return redirect()->back();
        }

        $header = $this->authHeaders();
        $website_url = URL::to("/");


        $body_data = array(
            'mode' => '1011',
            'payerReference' => '1', // pass oderId or anything you want
            'callbackURL' => $website_url . '/bkash-dynamic-callback',
            'amount' => $request->amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $request->merchantInvoiceNumber ? $request->merchantInvoiceNumber : "Inv_" . Str::random(6)  // should be dynamic
        );

        $response = $this->curlWithBody('/payment/create', $header, 'POST', json_encode($body_data));

        $res_array = json_decode($response, true);

        if (!array_key_exists("bkashURL", $res_array)) {
            return redirect()->back();
        }

        return redirect((json_decode($response)->bkashURL));

    }

    public function callback(Request $request)
    {
        $allRequest = $request->all();
        if (isset($allRequest['status']) && $allRequest['status'] == 'success') {
            $response = $this->executePayment($allRequest['paymentId']);

            if (is_null($response)) {
                sleep(1);
                $response = $this->queryPayment($allRequest['paymentId']);
            }

            $res_array = json_decode($response, true);

            if (array_key_exists("transactionStatus", $res_array) && $res_array['transactionStatus'] == 'Completed') {
                // payment success case
                return view('bkash.success')->with([
                    'response' => $res_array['trxID']
                ]);
            }

        } else {
            return view('bkash.fail')->with([
                'response' => 'Payment Failed !!',
            ]);
        }
        return view('bkash.fail')->with([
            'response' => 'Payment Failed !!',
        ]);

    }

    public function executePayment($paymentId)
    {

        $header = $this->authHeaders();

        $body_data = array(
            'paymentId' => $paymentId
        );

        $response = $this->curlWithBody('/payment/execute', $header, 'POST', json_encode($body_data));

        return $response;
    }
    public function queryPayment($paymentId)
    {
        $header = $this->authHeaders();

        $body_data = array(
            'paymentId' => $paymentId,
        );

        $response = $this->curlWithBody('/query/payment', $header, 'POST', json_encode($body_data));

        return $response;
    }

    public function getRefund(Request $request)
    {
        return view('bkash.refund');
    }

    public function refundPayment(Request $request)
    {
        $header = $this->authHeaders();

        $body_data = array(
            'paymentId' => $request->paymentID,
            'trxID' => $request->trxID
        );

        $response = $this->curlWithBody('/query/refund', $header, 'POST', json_encode($body_data));

        $res_array = json_decode($response, true);

        $message = "Something went wrong. Refund Failed !!";

        if (!isset($res_array['refundTrxId'])) {
            $body_data = array(
                'paymentId' => $request->paymentID,
                'amount' => $request->amount,
                'trxID' => $request->trxID,
                'sku' => 'sku',
                'reason' => 'reason'
            );

            $response = $this->curlWithBody('/payment/refund', $header, 'POST', json_encode($body_data));

            $res_array = json_decode($response, true);

            if (isset($res_array['refundTrxId'])) {
                // your database insert operation    
                $message = "Refund successful !!.Your Refund TrxID : " . $res_array['refundTrxId'];
            }

        } else {
            $message = "Already Refunded !! Your Refund TrxID : " . $res_array['refundTrxId'];
        }

        return view('bkash.refund')->with([
            'response' => $message,
        ]);
    }

    public function getSearchTransaction(Request $request)
    {
        return view('bkash.search');
    }

    public function searchTransaction(Request $request)
    {

        $header = $this->authHeaders();
        $body_data = array(
            'trxID' => $request->trxID,
        );

        $response = $this->curlWithBody('/search/transaction', $header, 'POST', json_encode($body_data));


        return view('bkash.search')->with([
            'response' => $response,
        ]);
    }

}
