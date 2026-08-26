<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Models\Concerns\TracksUserActivity;

class Product extends Model
{
    use HasFactory, TracksUserActivity;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'item_code',
        'description',
        'unit_price',
    ];

    /**
     * Products are private to the user who created them.
     * Administrators must follow this rule as well.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where('created_by', $user->id);
    }
}
