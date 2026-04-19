<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','category_id','title','amount',
        'cadence','interval','next_run_on','last_run_on','ends_on',
        'notes','active',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'next_run_on' => 'date',
        'last_run_on' => 'date',
        'ends_on'     => 'date',
        'active'      => 'boolean',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
}
