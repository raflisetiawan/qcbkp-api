<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityIssue extends Model
{
    use HasFactory;
    protected $fillable = [
        'issue_id',
        'user_id',
        'problem',
        'machine_performance',
        'trouble_duration_minutes',
        'solution',
        'impact',
        'closed',
        'closed_date',
        'todos',
        'quality_control_verification'
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}
