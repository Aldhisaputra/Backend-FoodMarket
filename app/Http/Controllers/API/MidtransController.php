<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        // Set konfigurasi Mitrans
        Config::$serverKey = config('service.midtrans.serverKey');
        Config::$isProduction = config('service.midtrans.isProduction');
        Config::$isSanitized = config('service.midtrans.isSanitized');
        Config::$is3ds = config('service.midtrans.is3ds');

        // Buat instance Midtrans notification
        $notification = new Notification();

        // Assign ke variabel untuk memudahkan koding
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrfail($order_id);

        // Handel transaksi status midtrans
        if($status =='capture')
        {
            if($type == 'credit_card')
            {
                if($fraud == 'challenge')
                {
                    $transaction->status = 'PENDING';
                }
                else
                {
                    $transaction->status = 'SUCCESS';
                }
            }
        }
        else if($status == 'settlement')
        {
            $transaction->status = 'SUCCESS';
        }
        else if($status == 'pending')
        {
            $transaction->status = 'PENDING';
        }
        else if($status == 'deny')
        {
            $transaction->status = 'CANCELLED';
        }
        else if($status == 'expire')
        {
            $transaction->status = 'CANCELLED';
        }
        else if($status == 'cancel')
        {
            $transaction->status = 'CANCELLED';
        }

        // Simpan transaksi
        $transaction->save();
    }
    public function success()
    {
        return view('midtrans.success');
    }
    public function unfinish()
    {
        return view('midtrans.unfinish');
    }
    public function error()
    {
        return view('midtrans.error');
    }
}
