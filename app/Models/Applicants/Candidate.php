<?php

namespace App\Models\Applicants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Candidate extends Model
{
    protected $table = 'candidates';
    
    protected $fillable = [
        'candidate_name',
        'candidate_email',
        'candidate_phone',
        'candidate_sex',
        'candidate_birth_date',
        'candidate_civil_status',
        'candidate_age',
        'candidate_region',
        'candidate_province',
        'candidate_city',
        'candidate_barangay',
        'candidate_house_street',
        'applied_position',
        'department',
        'rating_score',
        'rating_description',
        'skills',
        'experience',
        'education',
        'resume_url',
        'status',
        'interview_schedule',
        'scheduling_token',
        'self_scheduled',
        'interview_scores',
        'interview_total_score',
        'interview_result',
        'interview_notes',
        'interview_stage',
        'contract_status',
        'contract_sent_at',
        'contract_approved_at',
        'documents_email_sent',
        'documents_email_sent_at',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'skills' => 'array',
        'experience' => 'array',
        'education' => 'array',
        'interview_scores' => 'array',
        'interview_schedule' => 'datetime',
        'contract_sent_at' => 'datetime',
        'contract_approved_at' => 'datetime',
        'documents_email_sent_at' => 'datetime',
        'self_scheduled' => 'boolean',
        'documents_email_sent' => 'boolean',
        'rating_score' => 'decimal:2',
        'interview_total_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Generate a unique scheduling token for self-scheduling
     */
    public function generateSchedulingToken(): string
    {
        $this->scheduling_token = Str::random(32);
        $this->save();
        return $this->scheduling_token;
    }

    /**
     * Get rating description based on score
     */
    public static function getRatingDescription(float $score): string
    {
        if ($score >= 90) {
            return 'Exceptional - Outstanding qualifications, exceeds all requirements';
        } elseif ($score >= 80) {
            return 'Highly Qualified - Strong technical background and experience';
        } elseif ($score >= 70) {
            return 'Qualified - Meets requirements with good potential';
        } elseif ($score >= 60) {
            return 'Moderately Qualified - Meets basic requirements';
        } elseif ($score >= 50) {
            return 'Marginally Qualified - Some gaps in qualifications';
        } else {
            return 'Not Qualified - Does not meet minimum requirements';
        }
    }

    /**
     * Get rating badge color based on score
     */
    public static function getRatingBadgeColor(float $score): string
    {
        if ($score >= 80) {
            return 'success';
        } elseif ($score >= 60) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Get the scheduling URL for self-scheduling
     */
    public function getSchedulingUrl(): string
    {
        if (!$this->scheduling_token) {
            $this->generateSchedulingToken();
        }
        return url('/schedule-interview/' . $this->scheduling_token);
    }

    /**
     * Check if candidate can proceed to interview
     */
    public function canProceedToInterview(): bool
    {
        return $this->status === 'scheduled' && $this->interview_schedule !== null;
    }

    /**
     * Check if candidate passed interview
     */
    public function passedInterview(): bool
    {
        return $this->interview_result === 'passed';
    }

    /**
     * Check if candidate is in offering stage
     */
    public function isInOfferingStage(): bool
    {
        return $this->interview_result === 'passed' && in_array($this->contract_status, ['pending', 'sent', 'approved']);
    }

    /**
     * Get interview stage label
     */
    public static function getInterviewStageLabel(string $stage): string
    {
        $labels = [
            'initial' => 'Initial Interview',
            'practical' => 'Practical Exam',
            'demo' => 'Demo/Presentation',
        ];
        return $labels[$stage] ?? ucfirst($stage);
    }

    /**
     * Get interview stage description
     */
    public static function getInterviewStageDescription(string $stage): string
    {
        $descriptions = [
            'initial' => 'Tell me about yourself.',
            'practical' => 'Show me you can do the job.',
            'demo' => 'Convince me (and maybe others) with a live demonstration.',
        ];
        return $descriptions[$stage] ?? '';
    }

    /**
     * Get interview stage badge color
     */
    public static function getInterviewStageBadgeColor(string $stage): string
    {
        $colors = [
            'initial' => 'info',
            'practical' => 'warning',
            'demo' => 'success',
        ];
        return $colors[$stage] ?? 'secondary';
    }

    /**
     * Get interview stage icon
     */
    public static function getInterviewStageIcon(string $stage): string
    {
        $icons = [
            'initial' => 'bi-chat-dots',
            'practical' => 'bi-pencil-square',
            'demo' => 'bi-display',
        ];
        return $icons[$stage] ?? 'bi-question-circle';
    }
}
