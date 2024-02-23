<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'title',
        'memo'
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}
