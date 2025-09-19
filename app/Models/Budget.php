<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';

    protected $fillable = ['user_id','category_id','period','amount'];

    protected $casts = [
        'period' => 'date',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }

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
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }
}
