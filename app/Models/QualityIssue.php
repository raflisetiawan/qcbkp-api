<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QualityIssue extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($qualityIssue) {
            error_log($qualityIssue);
            // Check if discovery_file exists
            if ($qualityIssue->discovery_file) {
                error_log('ok');
                // Delete the discovery_file from storage
                Storage::disk('public')->delete("discovery_files/{$qualityIssue->discovery_file}");
                // Storage::delete("discovery_files/{$qualityIssue->discovery_file}");
            }
        });
    }


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
