<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'date',
        'activity',
        'hours',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
