<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','name','type','currency','starting_balance','archived',
    ];

    protected $casts = [
        'starting_balance' => 'decimal:2',
        'archived' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function expenses(): HasMany { return $this->hasMany(Expense::class); }

    public function incomingTransfers(): HasMany { return $this->hasMany(Transfer::class, 'to_account_id'); }
    public function outgoingTransfers(): HasMany { return $this->hasMany(Transfer::class, 'from_account_id'); }

    /**
     * Compute balance from ledger (starting + incoming transfers - outgoing transfers - expenses).
     * For reporting pages, consider pre-aggregating to avoid heavy sums across large datasets.
     */
    public function balance(): float
    {
        $expenses = (float) $this->expenses()->sum('amount');
        $incoming = (float) $this->incomingTransfers()->sum('amount');
        $outgoing = (float) $this->outgoingTransfers()->sum('amount');
        return (float) $this->starting_balance + $incoming - $outgoing - $expenses;
    }
}
