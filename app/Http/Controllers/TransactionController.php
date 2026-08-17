<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class TransactionController extends Controller
{
    /**
     * Display a listing of all payment transactions.
     */
    public function index()
    {
        $transactions = Transaction::with('user', 'order')->latest()->get();
        return view('transactions.index', compact('transactions'));
    }
}
