<div @class(['p-5', 'bg-light'])>
    
    {{-- Terms & Agreement Gate Modal --}}
    @if($showTermsGate)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="z-index: 9999; background-color: rgba(0,0,0,0.7);">
            <div class="card border-0 shadow-lg rounded-4" style="min-width: 500px; max-width: 700px; max-height: 90vh;">
                <div class="card-header bg-primary text-white py-4 rounded-top-4">
                    <div class="text-center">
                        <i class="bi bi-file-earmark-text" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold mb-0 mt-2">Terms & Conditions</h4>
                        <p class="mb-0 small opacity-75">Please read and accept before proceeding</p>
                    </div>
                </div>
                <div class="card-body p-4" style="max-height: 50vh; overflow-y: auto;">
                    <div class="bg-light p-4 rounded-3 border mb-4">
                        <h6 class="fw-bold text-primary"><i class="bi bi-shield-lock me-2"></i>JetLounge Travels Privacy Statement</h6>
                        <p class="small text-muted mb-3">
                            JetLounge Travels is committed to protecting the privacy and security of all applicants and employees. 
                            Any personal information you provide through this recruitment platform is collected, stored, and processed 
                            solely for legitimate employment and recruitment purposes. We strictly adhere to applicable cyber laws, 
                            including the <strong>Philippine Data Privacy Act of 2012</strong>, and international data protection standards such as 
                            the <strong>General Data Protection Regulation (GDPR)</strong>.
                        </p>
                        
                        <h6 class="fw-bold text-primary mt-4"><i class="bi bi-lock me-2"></i>Data Protection</h6>
                        <p class="small text-muted mb-3">
                            Your data will only be accessed by authorized HR personnel and will never be disclosed to third parties 
                            without your explicit consent, unless required by law. We implement industry-standard safeguards, including 
                            encryption, secure servers, and controlled access, to ensure that your information remains confidential 
                            and protected against unauthorized use, loss, or alteration.
                        </p>
                        
                        <h6 class="fw-bold text-primary mt-4"><i class="bi bi-clock-history me-2"></i>Data Retention</h6>
                        <p class="small text-muted mb-3">
                            By submitting your application, you acknowledge and agree that JetLounge Travels may retain your information 
                            for the duration of the recruitment process and, if successful, for the period of your employment. If your 
                            application is not successful, your data will be securely deleted or anonymized after a reasonable retention 
                            period, in compliance with legal and regulatory requirements.
                        </p>
                        
                        <h6 class="fw-bold text-primary mt-4"><i class="bi bi-person-check me-2"></i>Your Rights</h6>
                        <p class="small text-muted mb-0">
                            You have the right to access, correct, or request deletion of your personal data at any time. For concerns 
                            or inquiries regarding your information, please contact our Data Protection Officer. JetLounge Travels 
                            respects your rights as a data subject and ensures that all processing activities are transparent, fair, 
                            and lawful under applicable cyber laws.
                        </p>
                    </div>
                    
                    @error('termsAccepted')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms-accepted" wire:model.live="termsAccepted" style="width: 1.25em; height: 1.25em;">
                        <label class="form-check-label ms-2 fw-semibold" for="terms-accepted">
                            I have read, understood, and agree to the Terms & Conditions and Privacy Policy.
                        </label>
                    </div>
                </div>
                <div class="card-footer bg-white py-4 rounded-bottom-4">
                    <div class="d-grid gap-2">
                        <button type="button" 
                                wire:click="acceptTerms"
                                class="btn btn-primary btn-lg {{ !$termsAccepted ? 'disabled' : '' }}"
                                style="background-color: #213A5C; border: none;"
                                {{ !$termsAccepted ? 'disabled' : '' }}>
                            <i class="bi bi-check-circle me-2"></i>Accept and Continue
                        </button>
                        <a href="{{ route('careers') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    {{-- SUCCESS TOAST --}}
    @if($showSuccessToast)
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <div class="card border-0 shadow-lg bg-success text-white rounded-4 animate__animated animate__fadeInRight" 
                 style="min-width: 320px;"
                 wire:poll.5s="$set('showSuccessToast', false)">
                <div class="card-body d-flex align-items-center p-3">
                    <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Success!</h6>
                        <p class="mb-0 small">Application submitted successfully.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" wire:click="$set('showSuccessToast', false)"></button>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Content - Only show after Terms accepted --}}
    @if(!$showTermsGate)
    <a @class(['nav-link', 'text-secondary', 'mb-4']) href="{{ route('careers') }}">
        <i class="bi bi-arrow-left-circle me-2"></i>Back to Jobs
    </a>

    <div @class(['container', 'bg-white', 'p-5', 'rounded-4', 'shadow-sm'])>
        <div class="border-bottom mb-5 pb-3">
            <h2 class="fw-bold mb-1">Application Form</h2>
            <p class="text-muted">Position: <span style="color: #213A5C;" class="fw-semibold">{{ $job->position }}</span></p>
        </div>

        {{-- Show Database Errors if any --}}
        @error('submission')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        {{-- Show All Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="submitApplication">
            <x-honeypot />
            <div @class(['row', 'g-4'])>
                
                {{-- Personal Information --}}
                <div class="col-12">
                    <h5 class="text-uppercase tracking-wider text-secondary fw-bold small mb-1">Personal Information</h5>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>All fields are required. Only the <strong>Suffix</strong> field is optional.</p>
                </div>

                <div class="col-md-4">
                    <x-input-label for="first-name" :value="__('First Name')" />
                    <x-text-input wire:model.blur="applicantFirstName" type="text" id="first-name" class="form-control-lg" />
                    <x-input-error field="applicantFirstName" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="middle-name" :value="__('Middle Name')" />
                    <x-text-input wire:model.blur="applicantMiddleName" type="text" id="middle-name" class="form-control-lg" />
                    <x-input-error field="applicantMiddleName" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="last-name" :value="__('Last Name')" />
                    <x-text-input wire:model.blur="applicantLastName" type="text" id="last-name" class="form-control-lg" />
                    <x-input-error field="applicantLastName" />
                </div>

                <div class="col-md-2">
                    <x-input-label for="suffix-name" :value="__('Suffix (Optional)')" />
                    <x-text-input wire:model.blur="applicantSuffixName" type="text" id="suffix-name" class="form-control-lg" placeholder="Jr." />
                </div>

                <div class="col-md-2">
                    <x-input-label for="applicant-age" :value="__('Age')" />
                    <x-text-input wire:model.blur="applicantAge" type="number" id="applicant-age" class="form-control-lg" placeholder="25" />
                    <x-input-error field="applicantAge" />
                </div>

                <div class="col-md-2">
                    <x-input-label for="applicant-gender" :value="__('Gender')" />
                    <select wire:model.live="applicantGender" id="applicant-gender" class="form-select form-select-lg">
                        @php($genders = [''=>'Select Gender', 'male'=>'Male', 'female'=>'Female'])
                        @foreach($genders as $value => $label)
                            <option value="{{ $value }}" {{ $applicantGender == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error field="applicantGender" />
                </div>

                <div class="col-md-2">
                    <x-input-label for="applicant-civil-status" :value="__('Civil Status')" />
                    <select wire:model.live="applicantCivilStatus" id="applicant-civil-status" class="form-select form-select-lg">
                        <option value="">Select Status</option>
                        @foreach(['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                    <x-input-error field="applicantCivilStatus" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="applicant-dob" :value="__('Date of Birth')" />
                    <x-text-input wire:model.blur="applicantDateOfBirth" type="date" id="applicant-dob" class="form-control-lg" />
                    <x-input-error field="applicantDateOfBirth" />
                </div>

                <div class="col-md-3">
                    <x-input-label for="applicant-phone" :value="__('Phone Number')" />
                    <x-text-input wire:model.blur="applicantPhone" type="text" id="applicant-phone" class="form-control-lg" />
                    <x-input-error field="applicantPhone" />
                </div>

                <div class="col-md-12">
                    <x-input-label for="applicant-email" :value="__('Email Address')" />
                    <x-text-input wire:model.blur="applicantEmail" type="email" id="applicant-email" class="form-control-lg" />
                    <x-input-error field="applicantEmail" />
                </div>

                {{-- Address Section --}}
                <div class="col-12 mt-5">
                    <h5 class="text-uppercase tracking-wider text-secondary fw-bold small mb-3">Current Address</h5>
                </div>

                <div class="col-md-6">
                    <x-input-label for="region" :value="__('Region')" />
                    <select wire:model.live="selectedRegion" id="region" class="form-select form-select-lg">
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region['code'] }}">{{ $region['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error field="selectedRegion" />
                </div>

                @if((string) $selectedRegion !== '1300000000')
                <div class="col-md-6">
                    <x-input-label for="province" :value="__('Province')" />
                    <select wire:model.live="selectedProvince" id="province" class="form-select form-select-lg" {{ empty($provinces) ? 'disabled' : '' }}>
                        <option value="">Select Province</option>
                        @foreach($provinces as $province)
                            <option value="{{ $province['code'] }}">{{ $province['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error field="selectedProvince" />
                </div>
                @endif

                <div class="col-md-{{ (string) $selectedRegion === '1300000000' ? '12' : '6' }}">
                    <x-input-label for="city" :value="__('City / Municipality')" />
                    @if((string) $selectedRegion === '1300000000')
                        <small class="text-info d-block mb-2">NCR Cities Available: {{ count($cities) }}</small>
                    @endif
                    <select wire:model.live="selectedCity" id="city" class="form-select form-select-lg" {{ empty($cities) ? 'disabled' : '' }}>
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                            <option value="{{ $city['code'] }}">{{ $city['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error field="selectedCity" />
                </div>

                <div class="col-md-6">
                    <x-input-label for="barangay" :value="__('Barangay')" />
                    <select wire:model.live="selectedBarangay" id="barangay" class="form-select form-select-lg" {{ empty($barangays) ? 'disabled' : '' }}>
                        <option value="">Select Barangay</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay['name'] }}">{{ $barangay['name'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error field="selectedBarangay" />
                </div>

                <div class="col-12">
                    <x-input-label for="house-street" :value="__('House No. / Street')" />
                    <x-text-input wire:model.blur="houseStreet" type="text" id="house-street" class="form-control-lg" />
                    <x-input-error field="houseStreet" />
                </div>

                {{-- File Upload --}}
                <div class="col-12 mt-5">
                    <h5 class="text-uppercase tracking-wider text-secondary fw-bold small mb-3">Professional Documents</h5>
                    
                    {{-- Full-screen backdrop loader for file upload --}}
                    <div wire:loading wire:target="applicantResumeFile" class="upload-backdrop">
                        <div class="upload-loader-content">
                            <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="text-white fw-semibold mb-1">Uploading Resume</h5>
                            <p class="text-white-50 small mb-0">Please wait...</p>
                        </div>
                    </div>

                    @if (!$applicantResumeFile)
                        <label for="resume" class="w-100">
                            <input wire:model="applicantResumeFile" type="file" id="resume" class="d-none" accept=".pdf" />
                            <div class="upload-box d-flex flex-column justify-content-center align-items-center p-4 border border-2 border-dashed rounded-4 bg-light text-center" style="cursor: pointer; min-height: 150px;">
                                <i class="bi bi-file-earmark-pdf fs-1 text-primary mb-2"></i>
                                <span class="fw-bold">Click to upload Resume</span>
                                <small class="text-muted mt-2"><i class="bi bi-info-circle me-1"></i>Only PDF files are accepted (Max: 2MB)</small>
                            </div>
                        </label>
                    @else
                        <div class="alert alert-info d-flex justify-content-between align-items-center rounded-4">
                            <span><i class="bi bi-file-earmark-check me-2"></i>{{ $applicantResumeFile->getClientOriginalName() }}</span>
                            <button type="button" wire:click="removeResume" class="btn btn-sm btn-danger rounded-pill">Remove</button>
                        </div>
                    @endif
                    <x-input-error field="applicantResumeFile" />
                </div>

                <style>
                    .upload-backdrop {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        width: 100vw;
                        height: 100vh;
                        background-color: rgba(0, 0, 0, 0.7);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        z-index: 9999;
                        backdrop-filter: blur(4px);
                    }
                    .upload-loader-content {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        text-align: center;
                        padding: 2.5rem 3rem;
                        border-radius: 1rem;
                        background: rgba(33, 58, 92, 0.95);
                        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                    }
                </style>

                {{-- Submission --}}
                <div class="col-12 mt-5 border-top pt-4">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agreed-to-terms" wire:model.live="agreedToTerms">
                        <label class="form-check-label ms-2" for="agreed-to-terms">
                            I agree to the <a href="javascript:void(0)" wire:click="$toggle('showTerms')" class="fw-bold text-primary">terms and conditions</a>.
                        </label>
                    </div>
                    @error('agreedToTerms') <p class="text-danger small">{{ $message }}</p> @enderror

                    @if($showTerms)
                        <div class="bg-light p-4 rounded-3 border mb-4 shadow-sm animate__animated animate__fadeInUp">
                            <h6 class="fw-bold">Jetlouge Travels Privacy Statement</h6>
                                <p class="small text-muted">
                                    Jetlouge Travels is committed to protecting the privacy and security of all applicants and employees. 
                                    Any personal information you provide through this recruitment platform is collected, stored, and processed 
                                    solely for legitimate employment and recruitment purposes. We strictly adhere to applicable cyber laws, 
                                    including the Philippine Data Privacy Act of 2012, and international data protection standards such as 
                                    the General Data Protection Regulation (GDPR).
                                </p>
                                <p class="small text-muted">
                                    Your data will only be accessed by authorized HR personnel and will never be disclosed to third parties 
                                    without your explicit consent, unless required by law. We implement industry-standard safeguards, including 
                                    encryption, secure servers, and controlled access, to ensure that your information remains confidential 
                                    and protected against unauthorized use, loss, or alteration.
                                </p>
                                <p class="small text-muted">
                                    By submitting your application, you acknowledge and agree that Jetlouge Travels may retain your information 
                                    for the duration of the recruitment process and, if successful, for the period of your employment. If your 
                                    application is not successful, your data will be securely deleted or anonymized after a reasonable retention 
                                    period, in compliance with legal and regulatory requirements.
                                </p>
                                <p class="small text-muted">
                                    You have the right to access, correct, or request deletion of your personal data at any time. For concerns 
                                    or inquiries regarding your information, please contact our Data Protection Officer. Jetlouge Travels 
                                    respects your rights as a data subject and ensures that all processing activities are transparent, fair, 
                                    and lawful under applicable cyber laws.
                                </p>
                        </div>
                    @endif

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm" style="background-color: #213A5C; border: none;" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submitApplication">Submit Application</span>
                            <span wire:loading wire:target="submitApplication">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Submitting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
