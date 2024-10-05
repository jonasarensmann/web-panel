<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
