<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get the correct answer for this station.
     */
    public function correctAnswer()
    {
        return $this->belongsTo(Answer::class, 'answer_id');
    }
}
