<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Issue extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'plant_id',
        'shift',
        'issue_date',
        'quality_control_name',
        'qc_image',
    ];
    protected $appends = ['qc_image_path'];

    public function qualityIssue()
    {
        return $this->hasOne(QualityIssue::class, 'issue_id');
    }
    public function qualityIssues()
    {
        return $this->hasMany(QualityIssue::class, 'issue_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function getQcImagePathAttribute()
    {
        return $this->qc_image
            ? asset(Storage::url($this->qc_image))
            : null;
    }
}
