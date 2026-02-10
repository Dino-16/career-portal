<?php

namespace App\Traits;

use App\Models\Admin\HoneypotSetting;
use App\Models\Admin\HoneypotLog;

trait WithHoneypot
{
    public $honeypot_field = ''; // The actual value bound to the input

    public function checkHoneypot($formName = 'Unknown Form')
    {
        $setting = HoneypotSetting::first();
        
        if (!$setting || !$setting->is_enabled) {
            return true; // Bypass if disabled
        }

        if (!empty($this->honeypot_field)) {
            // Trap triggered!
            
            HoneypotLog::create([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'form_name' => $formName,
                'payload' => json_encode(['honeypot_value' => $this->honeypot_field]),
            ]);

            // Clear the field to reset UI if valid user mistakenly filled it (highly unlikely)
            $this->honeypot_field = ''; 

            // Stop execution by throwing error or just return false
            // Standard behavior: Fail silently or show generic error
            $this->addError('honeypot', 'Spam detected.'); 
            return false;
        }

        return true;
    }
}
