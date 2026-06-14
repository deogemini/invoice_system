<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TracksUserActivity;

class CompanySetting extends Model
{
    use HasFactory, TracksUserActivity;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'company_name',
        'tin_number',
        'p_o_box',
        'address',
        'phone',
        'email',
        'logo_path',
        'stamp_path',
    ];
}
