<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
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
