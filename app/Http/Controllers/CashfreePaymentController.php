<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CashfreePaymentController extends Controller
{
    public function create()
    {
        return view('Two.payment-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required',
            'mobile' => 'required|digits:10',
            'amount' => 'required'
        ]);

        $url = "https://sandbox.cashfree.com/pg/orders";

        $headers = array(
            "Content-Type: application/json",
            "x-api-version: 2023-08-01",
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
                "customer_phone" => $validated['mobile'],
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
        // dd(json_decode($resp));
        return redirect()->to(json_decode($resp)->payment_link);
    }

    public function success(Request $request)
    {

        // dd($request->all()); // PAYMENT STATUS RESPONSE
        $client = new Client();
        $response = $client->request('GET', "https://sandbox.cashfree.com/pg/orders/" . $request->get('order_id') . "order_id/settlements", [
            'headers' => [
                'accept' => 'application/json',
                'x-api-version' => '2023-08-01',
                "x-client-id" => env('CASHFREE_API_KEY'),
                "x-client-secret" => env('CASHFREE_API_SECRET')
            ]
        ]);
    }
}
