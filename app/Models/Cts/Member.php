<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $connection = 'cts';

    protected $attributes = ['old_rts_id' => 0];

    protected $guarded = [];

    public $timestamps = false;

    public $incrementing = false;
}
