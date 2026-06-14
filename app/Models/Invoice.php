<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TracksUserActivity;

class Invoice extends Model
{
    use HasFactory, TracksUserActivity;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'number',
        'customer_id',
        'bank_account_id',
        'date',
        'due_date',
        'sub_total',
        'discount',
        'include_vat',
        'vat_rate',
        'vat_amount',
        'total',
        'paid_amount',
        'status',
        'tra_status',
        'reference',
        'terms_and_conditions'
    ];

    protected $casts = [
        'include_vat' => 'boolean',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
