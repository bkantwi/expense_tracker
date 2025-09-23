<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','from_account_id','to_account_id','transferred_at','amount','memo',
    ];

    protected $casts = [
        'transferred_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function from() { return $this->belongsTo(Account::class, 'from_account_id'); }
    public function to()   { return $this->belongsTo(Account::class, 'to_account_id'); }
}
