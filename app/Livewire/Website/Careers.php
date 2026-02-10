<?php

namespace App\Livewire\Website;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Recruitment\JobListing;
use App\Models\Admin\RecaptchaSetting;
use App\Models\Admin\RecaptchaLog;
use Illuminate\Support\Facades\Http;

class Careers extends Component
{
    use WithPagination;

    public $showDetails = false;
    public $selectedJob;
    public $search = '';
    
    // reCAPTCHA properties
    public bool $showRecaptchaModal = true;
    public bool $recaptchaVerified = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // Check if reCAPTCHA is enabled
        $setting = RecaptchaSetting::first();
        if ($setting && !$setting->is_enabled) {
            $this->showRecaptchaModal = false;
            $this->recaptchaVerified = true;
        }
    }

    public function verifyRecaptcha($recaptchaResponse)
    {
        $secretKey = config('recaptcha.secret_key');
        
        try {
            $response = Http::withoutVerifying()->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $recaptchaResponse,
                'remoteip' => request()->ip(),
            ]);
            
            $result = $response->json();
            
            // Log the reCAPTCHA attempt
            RecaptchaLog::create([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => ($result['success'] ?? false) ? 'success' : 'failed',
            ]);
            
            if ($result['success'] ?? false) {
                $this->recaptchaVerified = true;
                $this->showRecaptchaModal = false;
            } else {
                $this->addError('recaptcha', 'reCAPTCHA verification failed. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            $this->addError('recaptcha', 'An error occurred during verification. Please try again.');
        }
    }

    public function viewDetails($id)
    {
        $this->selectedJob = JobListing::where('status', 'Active')->find($id);
        $this->showDetails = true;
    }

    public function remove()
    {
        $this->showDetails = false;
        $this->selectedJob = null;
    }

    // Manual search trigger
    public function searchJobs()
    {
        $this->resetPage();
    }

    // Reset pagination when search changes
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = JobListing::where('status', 'Active');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('position', 'like', "%{$this->search}%")
                  ->orWhere('type', 'like', "%{$this->search}%")
                  ->orWhere('arrangement', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        $jobs = $query->latest()->paginate(6);

        return view('livewire.website.careers', [
            'jobs' => $jobs,
            'selectedJob' => $this->selectedJob,
            'showDetails' => $this->showDetails
        ])->layout('layouts.website');
    }
}
