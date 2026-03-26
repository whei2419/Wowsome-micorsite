<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'client_id',
        'flower_id',
        'flower_name',
        'sender_name',
        'message',
        'sent_at',
    ];
}
