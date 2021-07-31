<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $craft_id = $request->input('craft_id');
        $status = $request->input('status');

        if ($id) 
        {
            $transaction = Transaction::with(['craft','user'])->find('$id');

            if ($transaction) 
            {
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            }
            else
            {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $transaction = Transaction::with(['food','user'])->where('user_id', Auth::user()->id);

        if ($craft_id) 
        {
            $transaction->where('craft_id',$craft_id);
        }

        if ($status) 
        {
            $transaction->where('status',$status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
        
    }

    public function update(Request $request, $id)
    {
        //mengambil data transaksi berdasarkan id
        $transaction = Transaction::findOrFail($id);

        //update data transaksi
        $transaction->update($request->all());

        //mengembalikan data updated
        return ResponseFormatter::success($transaction, 'Transaksi berhasil diperbarui');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'craft_id' => 'required|exists:craft,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required',
        ]);

        $transaction = Transaction::create([
            'craft_id' => $request->craft_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' =>'',
        ]);

        //Konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction'); 
        Config::$isSanitized = config('services.midtrans.isSanitized'); 
        Config::$is3ds = config('services.midtrans.is3ds'); 

        //Panggil transaksi yang tadi dibuat
        $transaction = Transaction::with(['craft','user'])->find($transaction->id);

        //Membuat transaksi midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ],
            'enabled_payments' => ['gopay','bank transfer'],
            'vtweb' => []
        ];

        //Memanggil midtrans
        try {
            //Ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            //Mengembalikan data ke API

            return ResponseFormatter::success($transaction, 'Transaksi Berhasil');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Transaksi Gagal');
        }

        //Mengembalikan data ke API
    }
}
