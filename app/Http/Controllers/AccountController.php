<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountStoreRequest;
use App\Http\Requests\AccountUpdateRequest;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', Auth::id())
            ->orderBy('archived')
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function store(AccountStoreRequest $request)
    {
        Account::create($request->validated() + ['user_id' => Auth::id()]);
        return back()->with('success', 'Account created.');
    }

    public function update(AccountUpdateRequest $request, Account $account)
    {
        $this->authorize('update', $account);
        $account->update($request->validated());
        return back()->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        // Prefer archiving to preserve history; but support hard delete if no links exist.
        if ($account->expenses()->exists() || $account->incomingTransfers()->exists() || $account->outgoingTransfers()->exists()) {
            return back()->withErrors(['account' => 'Account has activity. Archive instead of deleting.']);
        }

        $account->delete();
        return back()->with('success', 'Account deleted.');
    }
}
