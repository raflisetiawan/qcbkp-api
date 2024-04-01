<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'quality_control_verification',
        'discovery_file'
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    // protected function discoveryFile(): Attribute
    // {
    //     return Attribute::make(function ($discovery_file) {
    //         if ($discovery_file) {
    //             return asset('/storage/discovery_files/' . $discovery_file);
    //         } else {
    //             return null;
    //         }
    //     });
    // }
}
