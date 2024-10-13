<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cashfree\Cashfree;
use Cashfree\Model\CreateOrderRequest;
use Cashfree\Model\CustomerDetails;
use Cashfree\Model\OrderMeta;
use Exception;

class PaymentController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function initialPayment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'number' => 'required',
            'amount' => 'required'
        ]);

        // $cashfree->setClientId(env('CASHFREE_API_KEY'));
        // $cashfree->setClientSecret(env('CASHFREE_API_SECRET'));
        // $cashfree->setEnvironment(Cashfree::SANDBOX);


        // Cashfree::$XClientId = env('CASHFREE_API_ID');
        // Cashfree::$XClientSecret = env('CASHFREE_API_SECRET');
        // Cashfree::$XEnvironment = Cashfree::$SANDBOX;
        // Cashfree::$XEnvironment = Cashfree::$PRODUCTION;



        $url = "https://sandbox.cashfree.com/pg/orders";

        $headers = array(
            "Content-Type: application/json",
            "x-api-version: 2022-01-01",
            "x-client-id: " . env('CASHFREE_API_KEY'),
            "x-client-secret: " . env('CASHFREE_API_SECRET')
        );

        $data = json_encode([
            'order_id' =>  'order_' . rand(1111111111, 9999999999),
            'order_amount' => $validated['amount'],
            "order_currency" => "INR",
            "customer_details" => [
                "customer_id" => 'customer_' . rand(111111111, 999999999),
                "customer_name" => $validated['name'],
                "customer_email" => $validated['email'],
                "customer_phone" => $validated['number'],
            ],
            "order_meta" => [
                "return_url" => 'http://127.0.0.1:8000/cashfree/payments/success/?order_id={order_id}&order_token={order_token}'
            ]
        ]);

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($curl);

        curl_close($curl);
        // echo "<pre>";

        // dd(json_decode($resp));
        // echo "</pre>";
        return redirect()->to(json_decode($resp)->payment_link);



        try {
            dd($result);
            $payment_session_id = $result[0]['payment_session_id'];
            return view('payment-page', compact("result", "payment_session_id"));
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
    }

    public function PaymentSuccess($orderId)
    {
        $url = "https://sandbox.cashfree.com/pg/orders/$orderId";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'x-api-version: 2023-08-01',
            'Content-type: application/json',
            "x-client-id: " . env('CASHFREE_API_ID'),
            "x-client-secret: " . env("CASHFREE_API_SECRET")
        ]);

        $results = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return back()->withErrors("cURL Error: " . $error_msg);
        }

        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($results, true);

        // dd($response);
        return view('payment-success', compact('response'));
    }
}
