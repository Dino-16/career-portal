<div @class('py-5')>
    {{-- Google reCAPTCHA Script --}}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    {{-- reCAPTCHA Modal --}}
    @if($showRecaptchaModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="z-index: 9999; background-color: rgba(0,0,0,0.7);">
            <div class="card border-0 shadow-lg rounded-4" style="min-width: 450px; max-width: 550px;">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-shield-check text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Security Verification</h4>
                        <p class="text-muted">Please verify you're not a robot to view our career opportunities.</p>
                    </div>
                    
                    @error('recaptcha')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    @if(empty(config('recaptcha.site_key')))
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> reCAPTCHA site key is not configured.
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="{{ config('recaptcha.site_key') }}" style="transform: scale(0.9); transform-origin: 0 0;"></div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" 
                                onclick="verifyCareersRecaptcha()" 
                                class="btn btn-primary btn-lg"
                                style="background-color: #213A5C; border: none; transition: background-color 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#1a2d45';"
                                onmouseout="this.style.backgroundColor='#213A5C';">
                            <i class="bi bi-check-circle me-2"></i>Verify and Continue
                        </button>
                        <a href="#" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function verifyCareersRecaptcha() {
                const recaptchaResponse = grecaptcha.getResponse();
                if (recaptchaResponse) {
                    @this.verifyRecaptcha(recaptchaResponse);
                } else {
                    alert('Please complete the reCAPTCHA verification.');
                }
            }
        </script>
    @endif

    {{-- Main Content - Only show after reCAPTCHA verification --}}
    @if(!$showRecaptchaModal)
    <div @class('d-flex align-items-center justify-content-between flex-column mb-5')>
        <h1 @class('display-3 text-center fw-medium pt-5')>Apply Now and Be Part of Our Team</h1>
        <p @class('display-6 text-center')>Explore thousands of opportunities</p>

        <div @class('input-group mt-4 w-50')>
            <span @class('input-group-text bg-white border-end-0 no-focus') tabindex="0">
                <i @class('bi bi-search text-muted')></i>
            </span>
            <!-- Live search binding -->
            <input type="text" wire:model.live.debounce.500ms="search"
                @class('form-control border-start-0 no-focus no-hover')
                placeholder="Search jobs here ...">

            <!-- Manual search trigger -->
            <button wire:click="searchJobs" @class('btn btn-warning no-focus') type="button">
                Search
            </button>
        </div>
    </div>
    <div @class('container-fluid px-3 px-md-5 pt-5')>
        <h3 @class('mb-5 px-2 px-md-0')>All Available Jobs</h3>
        <div @class('row')>
            <!-- Job Listings Column -->
            <div @if($showDetails)
                @class('col-md-6')
                @else
                @class('col-md-12')
                @endif>
                <div @class('row g-4')>
                    @forelse($jobs as $job)
                        <div @class([
                            'col-12', 
                            'col-md-6', 
                            $showDetails ? 'col-lg-12 col-xl-6' : 'col-lg-4'
                        ])>
                            <div @class('card shadow-sm h-100 ' . ($selectedJob && $selectedJob->id === $job->id ? 'border border-3 border-warning' : ''))>
                                <div @class('card-body d-flex flex-column')>
                                    <h4 @class('h5 mb-3')>{{ $job->position }}</h4>
                                    <div @class('mb-3')>
                                        <span @class('badge bg-success me-1 mb-1')>{{ $job->type }}</span>
                                        <span @class('badge bg-primary')>{{ $job->arrangement }}</span>
                                    </div>
                                    <p @class('mb-auto')>{{ $job->description }}</p>

                                    @if($selectedJob && $selectedJob->id === $job->id)
                                        <button @class('btn btn-secondary mt-3') disabled>
                                            View Details <i @class('bi bi-arrow-right')></i>
                                        </button>
                                    @else
                                        <button wire:click="viewDetails({{ $job->id }})" @class('btn btn-primary mt-3')>
                                            View Details <i @class('bi bi-arrow-right')></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div @class('card p-5 d-flex align-items-center justify-content-center')>
                            <div>
                                <i @class('bi bi-search d-block fs-1 text-secondary mb-3 text-center')></i>
                                <h3 @class('text-center text-secondary')>No Available Jobs</h3>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($jobs->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $jobs->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
                    </div>
                @endif
            </div>

            <!-- Job Details Column -->
            @if($showDetails && $selectedJob)
                <div class="col-md-6 job-details-panel">
                    <div class="card border-0 shadow-lg overflow-hidden" style="animation: slideInRight 0.4s ease-out;">
                        {{-- Header --}}
                        <div class="card-header bg-white border-bottom py-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h4 class="mb-2 fw-bold">{{ $selectedJob->position }}</h4>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="bi bi-briefcase me-1"></i>{{ $selectedJob->type }}
                                        </span>
                                        <span class="badge bg-primary px-3 py-2">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $selectedJob->arrangement }}
                                        </span>
                                    </div>
                                </div>
                                <button wire:click="remove" class="btn btn-link text-secondary p-0 fs-4" style="transition: all 0.2s;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            {{-- Job Description --}}
                            <div class="mb-4" style="animation: fadeInUp 0.5s ease-out 0.1s both;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-file-text text-primary"></i>
                                    </div>
                                    <h5 class="mb-0 fw-semibold">Job Description</h5>
                                </div>
                                <p class="text-muted ps-4 ms-2 border-start border-2 border-primary">{{ $selectedJob->description }}</p>
                            </div>

                            {{-- Qualifications --}}
                            <div class="mb-4" style="animation: fadeInUp 0.5s ease-out 0.2s both;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-check2-circle text-warning"></i>
                                    </div>
                                    <h5 class="mb-0 fw-semibold">Qualifications</h5>
                                </div>
                                <div class="ps-4 ms-2 border-start border-2 border-warning">
                                    <p class="text-muted mb-0">{{ $selectedJob->qualifications }}</p>
                                </div>
                            </div>

                            {{-- Location if available --}}
                            @if($selectedJob->location)
                            <div class="mb-4" style="animation: fadeInUp 0.5s ease-out 0.3s both;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-pin-map text-success"></i>
                                    </div>
                                    <h5 class="mb-0 fw-semibold">Location</h5>
                                </div>
                                <p class="text-muted ps-4 ms-2 border-start border-2 border-success mb-0">{{ $selectedJob->location }}</p>
                            </div>
                            @endif

                            {{-- Apply Button --}}
                            <div class="mt-4 pt-3 border-top" style="animation: fadeInUp 0.5s ease-out 0.4s both;">
                                <a href="{{ route('apply-now', ['id' => $selectedJob->id]) }}" 
                                   class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2"
                                   style="transition: all 0.3s;">
                                    <i class="bi bi-send-fill"></i>
                                    Apply Now
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Animation Styles --}}
    <style>
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .job-details-panel .card:hover {
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
        }

        .job-details-panel .btn-link:hover {
            color: white !important;
            transform: rotate(90deg);
        }

        .job-details-panel .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4);
        }
    </style>
    @endif
</div>
