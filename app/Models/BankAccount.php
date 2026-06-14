<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TracksUserActivity;

class BankAccount extends Model
{
    use HasFactory, TracksUserActivity;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'bank_name',
        'account_name',
        'account_number',
        'swift_code',
        'currency'
    ];
}
