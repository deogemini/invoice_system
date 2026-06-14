<?php

namespace App\Models;

use App\Models\Concerns\TracksUserActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    use HasFactory, TracksUserActivity;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'delivery_note_id',
        'description',
        'quantity',
        'unit_price',
        'supplier_signature',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
