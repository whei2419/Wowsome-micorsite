<?php

namespace App\Models;

use App\Models\StationUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Station extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'question',
        'answer_id',
    ];

    public function stationUser()
    {
        return $this->hasMany(StationUser::class);
    }

}
