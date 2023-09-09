<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Payment\TripayController;

class TransactionController extends Controller
{
    public function show($reference)
    {
        $tripay = new TripayController();
        $detail = $tripay->detailTransaction($reference);

        return view('transaction.show', compact('tripay', 'detail'));
    }

    public function store(Request $request)
    {
        // Request Transaction in Tripay
        $book = Book::find($request->book_id);
        $method = $request->method;

        $tripay = new TripayController();
        $transaction = $tripay->requestTransaction($method, $book);

        // Create a new data in Transaction Model
        Transaction::create([
            'user_id' => auth()->user()->id,
            'book_id' => $book->id,
            'reference' => $transaction->reference,
            'merchant_ref' => $transaction->merchant_ref,
            'total_amount' => $transaction->amount,
            'status' => $transaction->status,
        ]);

        return redirect()->route('transaction.show', [
            'reference' => $transaction->reference,
        ]);
    }
}
