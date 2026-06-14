<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TracksUserActivity;

class Customer extends Model
{
    use HasFactory, TracksUserActivity;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'name',
        'email',
        'address',
        'tin',
        'phone',
        'p_o_box',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
