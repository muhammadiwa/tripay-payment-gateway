<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TripayController extends Controller
{
    public function getPaymentChanels()
    {
        $apiKey = config('tripay.api_key');
        // dd($apiKey);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/merchant/payment-channel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ));
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        $response = json_decode($response)->data;
        
        return $response ? $response : $error;
    }
    
    public function requestTransaction($method, $book)
    {
        $user         = auth()->user();
        $str          = Str::random(5);
        $apiKey       = config('tripay.api_key');
        $privateKey   = config('tripay.private_key');
        $merchantCode = config('tripay.merchant_code');
        $merchantRef  = 'INV-' .time();
        
        $data = [
            'method'         => $method,
            'merchant_ref'   => $merchantRef,
            'amount'         => $book->price,
            'customer_name'  => $user->name,
            'customer_email' => $user->email,
            // 'customer_phone' => '081234567890',
            'order_items'    => [
                [
                    // 'sku'         => 'FB-06',
                    'name'        => $book->title,
                    'price'       => $book->price,
                    'quantity'    => 1,
                    // 'product_url' => 'https://tokokamu.com/product/nama-produk-1',
                    // 'image_url'   => 'https://tokokamu.com/product/nama-produk-1.jpg',
                    ]
                ],
                'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
                'signature'    => hash_hmac('sha256', $merchantCode.$merchantRef.$book->price, $privateKey)
            ];
            
            $curl = curl_init();
            
            curl_setopt_array($curl, [
                CURLOPT_FRESH_CONNECT  => true,
                CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/transaction/create',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey],
                CURLOPT_FAILONERROR    => false,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($data),
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
            ]);
            
            $response = curl_exec($curl);
            $error = curl_error($curl);
            
            curl_close($curl);
            
            $response = json_decode($response)->data;
            // dd($response);
            
            return $response ?: $error;
            
        }
        
        public function detailTransaction($reference)
        {
            $apiKey = config('tripay.api_key');
            
            $payload = ['reference'	=> $reference];
            
            $curl = curl_init();
            
            curl_setopt_array($curl, [
                CURLOPT_FRESH_CONNECT  => true,
                CURLOPT_URL            => 'https://tripay.co.id/api-sandbox/transaction/detail?'.http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer '.$apiKey],
                CURLOPT_FAILONERROR    => false,
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
            ]);
            
            $response = curl_exec($curl);
            $error = curl_error($curl);
            
            curl_close($curl);
            
            $response = json_decode($response)->data;
            // dd($response);
            
            return $response ?: $error;
        }
    }
    