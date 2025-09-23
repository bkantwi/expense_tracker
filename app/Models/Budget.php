<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';

    protected $fillable = [
        'user_id','category_id','period','amount',
        'alerts_enabled','warn_threshold','at_threshold','over_threshold',
        'warn_sent_at','at_sent_at','over_sent_at', 'account_id'
    ];

    protected $casts = [
        'period'         => 'date',
        'amount'         => 'decimal:2',
        'alerts_enabled' => 'boolean',
        'warn_threshold' => 'integer',
        'at_threshold'   => 'integer',
        'over_threshold' => 'integer',
        'warn_sent_at'   => 'datetime',
        'at_sent_at'     => 'datetime',
        'over_sent_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Calculate how much was spent in this budget's month for its category.
     * NOTE: This runs a query; fine for first pass. We can batch-optimize later.
     */
    public function spent(): float
    {
        $start = $this->period->copy()->startOfMonth();
        $end   = $this->period->copy()->endOfMonth();

        return (float) \App\Models\Expense::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('account_id', $this->account_id)
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }
}
