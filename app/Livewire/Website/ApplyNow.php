<?php

namespace App\Livewire\Website;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Applicants\Application;
use App\Models\Applicants\FilteredResume;
use App\Models\Recruitment\JobListing;
use App\Data\NCRAddressData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use OpenAI\Laravel\Facades\OpenAI;

class ApplyNow extends Component
{
    use WithFileUploads;
    use \App\Traits\WithHoneypot;

    public $applicantLastName, $applicantFirstName, $applicantMiddleName, $applicantSuffixName, $applicantPhone, $applicantEmail, $applicantResumeFile;
    public $applicantAge, $applicantGender, $applicantCivilStatus, $applicantDateOfBirth;
    public $isUploading = false;
    public $lastAnalysisTime = null;
    public $job, $agreedToTerms = false, $showTerms = false, $showSuccessToast = false;
    public $regions = [], $provinces = [], $cities = [], $barangays = [];
    public $selectedRegion, $selectedProvince, $selectedCity, $selectedBarangay, $houseStreet;
    
    // Terms Agreement Gate
    public $showTermsGate = true;
    public $termsAccepted = false;

    public function mount($id)
    {
        $this->job = JobListing::findOrFail($id);

        try {
            $this->regions = Http::withoutVerifying()->get('https://psgc.cloud/api/regions')->json();
        } catch (\Exception $e) {
            $this->regions = [];
        }
    }

    /**
     * Accept terms and proceed to form
     */
    public function acceptTerms()
    {
        if (!$this->termsAccepted) {
            $this->addError('termsAccepted', 'You must accept the terms and conditions to continue.');
            return;
        }

        $this->showTermsGate = false;
    }

    /**
     * Go back from application form
     */
    public function goBack()
    {
        return redirect()->route('careers');
    }

    /**
     * Handle region selection change
     */
    public function updatedSelectedRegion($value)
    {
        // Reset dependent fields
        $this->provinces = [];
        $this->cities = [];
        $this->barangays = [];
        $this->selectedProvince = null;
        $this->selectedCity = null;
        $this->selectedBarangay = null;

        // Cast to string for reliable comparison (API may return int or string)
        $regionCode = (string) $value;

        \Log::info('Region selected', ['value' => $value, 'regionCode' => $regionCode, 'type' => gettype($value)]);

        if ($regionCode === '1300000000') {
            // NCR - Load cities directly from NCRAddressData
            $ncrCities = NCRAddressData::getCitiesAndBarangays();
            $this->cities = $ncrCities;
            \Log::info('NCR cities loaded', ['count' => count($ncrCities), 'cities' => collect($ncrCities)->pluck('name')]);
        } elseif (!empty($value)) {
            // Other regions - Fetch provinces from API
            try {
                $this->provinces = Http::withoutVerifying()->get("https://psgc.cloud/api/regions/{$value}/provinces")->json();
            } catch (\Exception $e) {
                $this->provinces = [];
            }
        }
    }

    /**
     * Handle province selection change
     */
    public function updatedSelectedProvince($value)
    {
        // Reset dependent fields
        $this->cities = [];
        $this->barangays = [];
        $this->selectedCity = null;
        $this->selectedBarangay = null;

        if (!empty($value)) {
            try {
                $this->cities = Http::withoutVerifying()->get("https://psgc.cloud/api/provinces/{$value}/cities-municipalities")->json();
            } catch (\Exception $e) {
                $this->cities = [];
            }
        }
    }

    /**
     * Handle city selection change
     */
    public function updatedSelectedCity($value)
    {
        // Reset dependent fields
        $this->barangays = [];
        $this->selectedBarangay = null;

        if ((string) $this->selectedRegion === '1300000000') {
            // NCR - Load barangays from NCRAddressData
            $ncrCities = NCRAddressData::getCitiesAndBarangays();
            $selectedCity = collect($ncrCities)->firstWhere('code', $value);
            
            if ($selectedCity && isset($selectedCity['barangays'])) {
                $this->barangays = collect($selectedCity['barangays'])->map(function ($barangay) {
                    return ['name' => $barangay];
                })->toArray();
            }
        } elseif (!empty($value)) {
            // Other regions - Fetch barangays from API
            try {
                $this->barangays = Http::withoutVerifying()->get("https://psgc.cloud/api/cities-municipalities/{$value}/barangays")->json();
            } catch (\Exception $e) {
                $this->barangays = [];
            }
        }
    }

    /**
     * Remove the uploaded resume file
     */
    public function removeResume()
    {
        $this->applicantResumeFile = null;
    }

    public function submitApplication()
    {
        // Increase execution time limit for AI analysis (2 minutes)
        set_time_limit(120);

        // Honeypot Check
        if (!$this->checkHoneypot('Job Application Form')) {
            return;
        }

        $this->validate([
            'applicantLastName' => 'required|max:50',
            'applicantFirstName' => 'required|max:50',
            'applicantMiddleName' => 'required|max:50',
            'applicantEmail' => 'required|email',
            'applicantPhone' => 'required',
            'applicantAge' => 'required|integer|min:18|max:65',
            'applicantDateOfBirth' => 'required|date|before:today',
            'applicantGender' => 'required|in:male,female',
            'applicantCivilStatus' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            'applicantResumeFile' => 'required|file|mimes:pdf|max:2048',
            'selectedRegion' => 'required',
            'selectedProvince' => 'required_unless:selectedRegion,1300000000',
            'selectedCity' => 'required',
            'selectedBarangay' => 'required',
            'houseStreet' => 'required',
            'agreedToTerms' => 'accepted',
        ]);

        DB::beginTransaction();

        try {
            $regionName = collect($this->regions)->firstWhere('code', $this->selectedRegion)['name'] ?? $this->selectedRegion;
            $provinceName = ($this->selectedRegion === '1300000000') ? 'NCR' : (collect($this->provinces)->firstWhere('code', $this->selectedProvince)['name'] ?? $this->selectedProvince);
            $cityName = $this->resolveCityName($this->selectedRegion, $this->selectedCity);

            $path = $this->applicantResumeFile->store('resumes', 'public');

            // Save application first
            $application = Application::create([
                'applied_position' => $this->job->position,
                'department'       => $this->job->department,
                'first_name'       => $this->applicantFirstName,
                'middle_name'      => $this->applicantMiddleName,
                'last_name'        => $this->applicantLastName,
                'suffix_name'      => $this->applicantSuffixName,
                'age'              => $this->applicantAge,
                'date_of_birth'    => $this->applicantDateOfBirth,
                'gender'           => $this->applicantGender,
                'civil_status'     => $this->applicantCivilStatus,
                'email'            => $this->applicantEmail,
                'phone'            => $this->applicantPhone,
                'region'           => $regionName,
                'province'         => $provinceName,
                'city'             => $cityName,
                'barangay'         => $this->selectedBarangay,
                'house_street'     => $this->houseStreet,
                'resume_path'      => $path,
                'agreed_to_terms'  => $this->agreedToTerms,
            ]);

            // --- Run Local AI analysis immediately (synchronous) ---
            $resumeContent = null;
            $filePath = storage_path('app/public/' . $path);
            
            \Log::info("Starting AI extraction for Application #{$application->id} using file: {$filePath}");

            if (!file_exists($filePath)) {
                \Log::error("Resume file not found at: {$filePath}");
            } else {
                try {
                    // 1. Try smalot/pdfparser
                    $parser = new Parser();
                    $pdf    = $parser->parseFile($filePath);
                    $resumeContent = $pdf->getText();
                    
                    if (empty(trim($resumeContent))) {
                        \Log::info("PdfParser main getText empty, trying page-by-page...");
                        $pages = $pdf->getPages();
                        $resumeContent = "";
                        foreach ($pages as $page) {
                            $resumeContent .= $page->getText() . "\n";
                        }
                    }

                    \Log::info("Extracted text length: " . strlen($resumeContent));
                } catch (\Exception $e) {
                    \Log::warning("Smalot PDF Parser failed for #{$application->id}: " . $e->getMessage());
                }
            }

            // --- AI Extraction & Rating Engine ---
            \Log::info("Attempting AI analysis for #{$application->id}...");
            $extraction = $this->analyzeResumeWithAI($resumeContent ?? '', $application->applied_position);
            
            $skills      = $extraction['skills'];
            $experience  = $extraction['experience'];
            $education   = $extraction['education'];
            $ratingScore = $extraction['score'];

            // Always calculate qualification status from score to ensure alignment
            if ($ratingScore >= 90) $qualificationStatus = 'Exceptional';
            elseif ($ratingScore >= 80) $qualificationStatus = 'Highly Qualified';
            elseif ($ratingScore >= 70) $qualificationStatus = 'Qualified';
            elseif ($ratingScore >= 60) $qualificationStatus = 'Moderately Qualified';
            elseif ($ratingScore >= 50) $qualificationStatus = 'Marginally Qualified';
            else $qualificationStatus = 'Not Qualified';

            // Create the filtered resume record
            $application->filteredResume()->create([
                'skills'               => $skills,
                'experience'           => $experience,
                'education'            => $education,
                'rating_score'         => (int) $ratingScore,
                'qualification_status' => $qualificationStatus,
            ]);

            if (empty(trim($resumeContent ?? ''))) {
                \Log::warning("Record created with empty text extraction for #{$application->id}. Scanned PDF suspected.");
            }

            DB::commit();

            $this->showSuccessToast = true;

            $this->reset([
                'applicantLastName', 'applicantFirstName', 'applicantMiddleName',
                'applicantSuffixName', 'applicantPhone', 'applicantEmail',
                'applicantAge', 'applicantGender', 'applicantCivilStatus', 'applicantDateOfBirth',
                'applicantResumeFile', 'selectedRegion', 'selectedProvince',
                'selectedCity', 'selectedBarangay', 'houseStreet', 'agreedToTerms'
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error("Submission error for app: " . $e->getMessage());
            $this->addError('submission', 'Application Error: ' . $e->getMessage());
        }
    }

    private function extractAgeFromResume(string $text): ?int
    {
        if (trim($text) === '') {
            return null;
        }

        // Pattern 1: Age: 27 or Age - 27
        if (preg_match('/\bage\s*[:\-]?\s*(\d{1,2})\b/i', $text, $m)) {
            $age = (int) $m[1];
            if ($age >= 15 && $age <= 70) {
                return $age;
            }
        }

        // Pattern 2: Date of Birth ... 1999 or DOB: 1999
        if (preg_match('/\b(?:date of birth|dob|birth date|born)\b[^0-9]*(\d{4})/i', $text, $m)) {
            $year = (int) $m[1];
            $currentYear = now()->year;
            $age = $currentYear - $year;
            if ($age >= 15 && $age <= 70) {
                return $age;
            }
        }

        return null;
    }

    private function calculateHeuristicScore(string $text, string $position): int
    {
        if (empty(trim($text))) return 0;

        $score = 50; // Base score if text exists
        $textLower = strtolower($text);
        $posLower = strtolower($position);

        // 1. Position match in text (+15)
        if (str_contains($textLower, $posLower)) {
            $score += 15;
        }

        // 2. Keyword matching based on common job terms (+5 to +20)
        $keywords = [
            'experience' => 5,
            'skills' => 5,
            'education' => 5,
            'degree' => 5,
            'certified' => 5,
            'management' => 3,
            'technical' => 3,
            'proficient' => 2,
        ];

        foreach ($keywords as $word => $points) {
            if (str_contains($textLower, $word)) {
                $score += $points;
            }
        }

        // 3. Length heuristic (too short is suspicious)
        if (strlen($text) < 500) {
            $score -= 10;
        } elseif (strlen($text) > 2000) {
            $score += 5;
        }

        return min(max($score, 0), 75); // Cap at 75 for manual heuristic to encourage AI verification
    }

    private function parseResumeSections(string $text): array
    {
        $sections = [
            'skills' => [],
            'experience' => [],
            'education' => [],
        ];

        if (trim($text) === '') {
            return $sections;
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $normalized);
        $current = null;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') continue;

            $lower = strtolower($trim);
            $normalizedHeaderLine = preg_replace('/\s+/', '', $lower); 
            
            // Detection with flexible regex
            if (preg_match('/^(?:professional|technical|hard|soft|key|core)?\s*(?:skills|competencies|technologies|tech\s+stack)\s*[:\-]?$/i', $trim) || 
                in_array($normalizedHeaderLine, ['skills', 'technicalskills', 'coreskills', 'keycompetencies'])) {
                $current = 'skills'; continue;
            }

            if (preg_match('/^(?:work|professional|employment)?\s*(?:experience|history|background)\s*[:\-]?$/i', $trim) ||
                in_array($normalizedHeaderLine, ['experience', 'workexperience', 'employmenthistory', 'workhistory'])) {
                $current = 'experience'; continue;
            }

            if (preg_match('/^(?:educational|academic)?\s*(?:education|background|history|records|academics)\s*[:\-]?$/i', $trim) ||
                in_array($normalizedHeaderLine, ['education', 'educationalbackground', 'academichistory'])) {
                $current = 'education'; continue;
            }

            if ($current !== null) {
                $item = preg_replace('/^[\-\*•]+\s*/u', '', $trim);
                if (strlen($item) > 2 && !preg_match('/^(?:references|hobbies|personal|interests)$/i', $item)) {
                    $sections[$current][] = $item;
                    if (count($sections[$current]) > 15) $current = null; // Prevent runaway sections
                }
            }
        }

        return $sections;
    }

    private function analyzeResumeWithAI(string $text, string $targetPosition): array
    {
        if (empty(trim($text))) {
            \Log::warning("Resume text is empty. PDF is likely a scanned image. Falling back to local engine.");
            return $this->runLocalAIEngine('', $targetPosition);
        }

        try {
            $apiKey = config('openai.api_key');
            if (empty($apiKey)) {
                $apiKey = env('OPENAI_API_KEY'); // Fallback to env if config fails
            }
            
            if (empty($apiKey)) {
                throw new \Exception("OpenAI API key is missing.");
            }

            \Log::info("Calling OpenAI API via Http facade (bypassing SSL)...");
            
            $prompt = "Analyze the following resume text for the position of: {$targetPosition}.
            Extract the information in JSON format with exactly these keys:
            - skills (array of strings)
            - experience (array of objects with keys: title, company, period, description)
            - education (array of objects with keys: degree, field, institution, year)
            - score (integer from 0 to 100 based on how well the candidate matches the position)
            
            Resume Text:
            {$text}";

            $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                ->timeout(60)
                ->withoutVerifying()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an HR expert AI that extracts resume data into structured JSON.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 1500,
                ]);

            if (!$response->successful()) {
                throw new \Exception("OpenAI API call failed: " . $response->body());
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;
            $result = json_decode($content, true);

            if (!$result || !isset($result['score'])) {
                throw new \Exception("Invalid AI response format.");
            }

            \Log::info("OpenAI analysis successful via Http. Score: {$result['score']}");
            return $result;

        } catch (\Exception $e) {
            \Log::error("OpenAI analysis failed: " . $e->getMessage() . ". Falling back to local heuristic engine.");
            return $this->runLocalAIEngine($text, $targetPosition);
        }
    }

    private function runLocalAIEngine(string $text, string $targetPosition): array
    {
        if (empty(trim($text))) {
            return [
                'skills' => ['Manual Review Required'],
                'experience' => [[
                    'title' => 'Review Work History', 
                    'company' => 'Information not extractable', 
                    'period' => 'N/A', 
                    'description' => 'The PDF content could not be read automatically (likely a scanned image). Please check the resume file manually.'
                ]],
                'education' => [[
                    'degree' => 'Review Education History', 
                    'field' => 'Information not extractable', 
                    'institution' => 'Manual Review Required', 
                    'year' => 'N/A'
                ]],
                'score' => 0
            ];
        }

        $textLower = strtolower($text);
        
        // 1. Extract Skills
        $skills = $this->extractSkillsLocally($textLower);
        
        // 2. Extract Experience
        $experience = $this->extractExperienceLocally($text);
        
        // 3. Extract Education
        $education = $this->extractEducationLocally($text);
        
        // 4. Calculate Rating
        $score = $this->calculateLocalRating($textLower, $targetPosition, $skills, $experience, $education);

        return [
            'skills' => count($skills) > 0 ? $skills : ['Review skills in resume'],
            'experience' => count($experience) > 0 ? $experience : [['title' => 'Review work history in resume', 'company' => 'N/A', 'period' => 'N/A', 'description' => '']],
            'education' => count($education) > 0 ? $education : [['degree' => 'Review education in resume', 'field' => 'N/A', 'institution' => 'N/A', 'year' => 'N/A']],
            'score' => $score
        ];
    }

    private function extractSkillsLocally(string $text): array
    {
        $skillDictionary = [
            'PHP', 'Laravel', 'JavaScript', 'React', 'Vue', 'Node.js', 'Python', 'Java', 'C++', 'C#', 
            'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'AWS', 'Docker', 'Kubernetes', 'HTML', 'CSS', 
            'Tailwind', 'Bootstrap', 'Git', 'Agile', 'Scrum', 'Management', 'Communication', 'Teamwork',
            'Problem Solving', 'Analysis', 'Marketing', 'Sales', 'Design', 'Photoshop', 'UI/UX',
            'Customer Service', 'Data Entry', 'Accounting', 'Bookkeeping', 'Office', 'Excel', 'Word',
            'Admin', 'Secretary', 'HR', 'Recruitment', 'Operations', 'Logistic', 'Supervisor', 'Lead'
        ];

        $found = [];
        $text = strtolower($text);
        foreach ($skillDictionary as $skill) {
            $s = strtolower($skill);
            // If skill has special chars (like C++, .js), don't use strict \b boundaries on both sides
            if (preg_match('/[+#.]/', $s)) {
                if (stripos($text, $s) !== false) {
                    $found[] = $skill;
                }
            } else {
                if (preg_match('/\b' . preg_quote($s, '/') . '\b/', $text)) {
                    $found[] = $skill;
                }
            }
        }
        return array_unique($found);
    }

    private function extractExperienceLocally(string $text): array
    {
        $experiences = [];
        // Regex to find potential job blocks (Title followed by Company and dates)
        // Simplified: Search for lines that look like Experience items
        $lines = explode("\n", $text);
        $currentExp = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Look for Date Patterns: (2018 - 2021) or (Jan 2018 - Present)
            if (preg_match('/\b(20\d{2}|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[^0-9\n]{1,20}(20\d{2}|Present|Current)\b/i', $line, $matches)) {
                if ($currentExp) $experiences[] = $currentExp;
                $currentExp = ['title' => 'Position Found', 'company' => 'Company Found', 'period' => $matches[0], 'description' => ''];
                continue;
            }

            if ($currentExp && empty($currentExp['description'])) {
                if (strlen($line) < 50) {
                    $currentExp['title'] = $line;
                } else {
                    $currentExp['description'] = substr($line, 0, 100) . '...';
                }
            }
        }
        if ($currentExp) $experiences[] = $currentExp;

        return array_slice($experiences, 0, 5);
    }

    private function extractEducationLocally(string $text): array
    {
        $education = [];
        $longDegrees = ['Bachelor', 'Master', 'Doctorate', 'College', 'University', 'High School'];
        $shortDegrees = ['BS', 'BA', 'MS', 'MA', 'PhD'];
        
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $matched = false;
            // Check long names (partial match okay)
            foreach ($longDegrees as $degree) {
                if (stripos($line, $degree) !== false) {
                    $matched = true;
                    break;
                }
            }
            // Check short names (strict word boundaries)
            if (!$matched) {
                foreach ($shortDegrees as $degree) {
                    if (preg_match('/\b' . preg_quote($degree, '/') . '\b/i', $line)) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $education[] = [
                    'degree' => $line,
                    'field' => 'See resume for details',
                    'institution' => 'Institution found in resume',
                    'year' => 'N/A'
                ];
            }
            if (count($education) >= 3) break;
        }
        return $education;
    }

    private function calculateLocalRating(string $text, string $pos, array $skills, array $exp, array $edu): int
    {
        $score = 40; // Base
        $textLower = strtolower($text);
        $posLower = strtolower($pos);

        // 1. Position match (+15 for partial, +25 for exact in text)
        if (str_contains($textLower, $posLower)) {
            $score += 25;
        } else {
            // Check for partial match (split position into words)
            $posWords = explode(' ', $posLower);
            foreach ($posWords as $word) {
                if (strlen($word) > 3 && str_contains($textLower, $word)) {
                    $score += 10;
                    break;
                }
            }
        }

        // 2. Skills count (+up to 20)
        $score += min(count($skills) * 3, 20);

        // 3. Experience count (+up to 10)
        $score += min(count($exp) * 3, 10);

        // 4. Education level (+up to 5)
        if (stripos($textLower, 'master') !== false || stripos($textLower, 'phd') !== false) {
            $score += 5;
        } elseif (stripos($textLower, 'bachelor') !== false || stripos($textLower, 'college') !== false) {
            $score += 3;
        }

        return min(max($score, 10), 98);
    }

    private function resolveCityName($region, $cityCode)
    {
        if ($region === '1300000000') {
            $ncrCityNames = [
                '137504000' => 'Caloocan City',
                '137506000' => 'Las Piñas City',
                '137507000' => 'Makati City',
                '137508000' => 'Malabon City',
                '137509000' => 'Mandaluyong City',
                '137501000' => 'Manila',
                '137511000' => 'Marikina City',
                '137512000' => 'Muntinlupa City',
                '137513000' => 'Navotas City',
                '137514000' => 'Parañaque City',
                '137515000' => 'Pasay City',
                '137516000' => 'Pasig City',
                '137502000' => 'Quezon City',
                '137517000' => 'San Juan City',
                '137518000' => 'Taguig City',
                '137519000' => 'Valenzuela City',
                '137520000' => 'Pateros'
            ];
            return $ncrCityNames[$cityCode] ?? $cityCode;
        }
        return collect($this->cities)->firstWhere('code', $cityCode)['name'] ?? $cityCode;
    }

    public function render()
    {
        return view('livewire.website.apply-now')->layout('layouts.website');
    }
}
