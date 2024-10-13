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
        $validate = $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'number' => 'required',
            'amount' => 'required'
        ]);

        $cashfree = new Cashfree();
        $cashfree->setClientId(env('CASHFREE_API_ID'));
        $cashfree->setClientSecret(env('CASHFREE_API_SECRET'));
        $cashfree->setEnvironment(Cashfree::SANDBOX);


        // Cashfree::$XClientId = env('CASHFREE_API_ID');
        // Cashfree::$XClientSecret = env('CASHFREE_API_SECRET');
        // Cashfree::$XEnvironment = Cashfree::$SANDBOX;
        // Cashfree::$XEnvironment = Cashfree::$PRODUCTION;


        $order_id = "inv_" . date('YmdHis');
        $order_amount = $validate['amount'];
        $customerID = "customer_" . rand(11111, 99999);
        $return_url = "http://127.0.0.1:8000/success" . $order_id;

        $x_api_version = "2023-08-01";
        $order_note = 'Order note for reference';
        $customer_phone = $validate['number'];
        $customer_email = $validate['email'];
        $customer_name = $validate['name'];



        $create_orders_request = new CreateOrderRequest();
        $create_orders_request->setOrderId($order_id);
        $create_orders_request->setOrderAmount($order_amount);
        $create_orders_request->setOrderCurrency('INR');

        $customer_details = new CustomerDetails();
        $customer_details->setCustomerId($customerID);
        $customer_details->setCustomerPhone($customer_phone);
        $customer_details->setCustomerEmail($customer_email);
        $customer_details->setCustomerName($customer_name);
        $create_orders_request->setCustomerDetails($customer_details);


        $order_meta = new OrderMeta();
        $order_meta->setReturnUrl($return_url);
        $create_orders_request->setOrderMeta($order_meta);

        try {
            $result = $cashfree->PGCreateOrder($x_api_version, $create_orders_request);
            $payment_session_id = $result[0]['payment_session_id'];
            // dd($result);
            return view('payment-page', compact("result", "payment_session_id"));
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage();
        }
      }

      public function PaymentSuccess($orderId){
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
            "x-client-id: ".env('CASHFREE_API_ID'),
            "x-client-secret: ". env("CASHFREE_API_SECRET")
        ]);

        $results = curl_exec($ch);
        if(curl_errno($ch)){
            $error_msg = curl_error($ch);
            curl_close($ch);
            return back()->withErrors("cURL Error: " . $error_msg);
        }

        $returnCode =curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($results, true);

        // dd($response);
        return view('payment-success', compact('response'));
      }
}
