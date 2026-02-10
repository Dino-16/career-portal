<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google reCAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your reCAPTCHA settings. The site key is used
    | on the frontend to render the reCAPTCHA widget, and the secret key
    | is used on the backend to verify responses.
    |
    */

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),

    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

];
