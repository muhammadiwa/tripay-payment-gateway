<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Payment\TripayController;
use App\Models\Book;

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
        $book = Book::find($request->book_id);
        $method = $request->method;

        $tripay = new TripayController();
        $transaction = $tripay->requestTransaction($method, $book);

        return redirect()->route('transaction.show', [
            'reference' => $transaction->reference,
        ]);
    }
}
