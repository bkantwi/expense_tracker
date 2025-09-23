<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferStoreRequest;
use App\Models\Account;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function store(TransferStoreRequest $request)
    {
        $data = $request->validated();

        // Verify both accounts belong to the current user and share the same currency (MVP)
        $accounts = Account::whereIn('id', [$data['from_account_id'], $data['to_account_id']])
            ->where('user_id', Auth::id())
            ->get()
            ->keyBy('id');

        if ($accounts->count() !== 2) {
            return back()->withErrors(['from_account_id' => 'Invalid accounts.']);
        }

        $from = $accounts[$data['from_account_id']];
        $to   = $accounts[$data['to_account_id']];

        if ($from->currency !== $to->currency) {
            return back()->withErrors(['amount' => 'Currency mismatch between accounts.']);
        }

        DB::transaction(function () use ($data) {
            Transfer::create($data + ['user_id' => Auth::id()]);
            // Balances are computed, so no direct mutation here.
        });

        return back()->with('success', 'Transfer recorded.');
    }
}
