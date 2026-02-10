<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- Title --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Jetlouge Travels</title>

    {{-- styles|scripts --}}
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="stylesheet" href="{{ asset('css/website.css') }}">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
   <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
    @livewireScripts
</head>
<body @class('m-0 p-0 d-flex flex-column min-vh-100')>
    {{-- Header - Hidden on apply-now page for cleaner application experience --}}
    @unless(request()->routeIs('apply-now'))
    <div @class('container')>
        <header @class('sticky-top position-absolute container')>
            <nav @class('navbar navbar-expand-lg p-1')>
                <div @class('container d-flex justify-content-between align-items-center')>
                    <img src="{{ asset('images/logo.png') }}" width="50" height="50" alt="Logo">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div>
                        <h3>Jetlouge Travels</h3>
                    </div>
                </div>
            </nav>
        </header>

    </div>
    @endunless
    {{-- Main --}}
    <main @class('flex-grow-1')>
        {{ $slot }}
    </main>

    <footer @class([
    'bg-dark',
    'text-white',
    'pt-5',
    'pb-4',
    'mt-auto'])>
        <div class="container">
            <div class="row">
            {{-- Product Column --}}
            <div @class('col-md-3')>
                <h5 @class('text-uppercase mb-4')>Product</h5>
                <ul @class('list-unstyled')>
                    <li><a href="#" @class('text-white text-decoration-none')>Book a Trip</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Tour Packages</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Flight and Hotel Deals</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Special Discounts</a></li>
                </ul>
            </div>

            {{-- Resources Column --}}
            <div @class('col-md-3')>
                <h5 @class('text-uppercase mb-4')>Resources</h5>
                <ul @class('list-unstyled')>
                    <li><a href="#" @class('text-white text-decoration-none')>Travel Blog</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Destination Guides</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Customer Support</a></li>
                    <li><a href="#" @class('text-white text-decoration-none')>Travel Tips</a></li>
                </ul>
            </div>

            {{-- Company Column --}}
            <div @class('col-md-3')>
                <h5 @class('text-uppercase mb-4')>Company</h5>
                <ul @class('list-unstyled')>
                <li><a href="#" @class('text-white text-decoration-none')>About Us</a></li>
                <li><a href="#" @class('text-white text-decoration-none')>Why Choose Us</a></li>
                <li><a href="#" @class('text-white text-decoration-none')>Contact Us</a></li>
                <li><a href="#" @class('text-white text-decoration-none')>Careers</a></li>
                </ul>
            </div>

            {{-- CTA Box --}}
            <div @class('col-md-3 d-flex align-items-center')>
                <div @class('bg-primary text-white p-4 rounded w-100 text-center')>
                <h5 @class('mb-0')>Travel with us Today!</h5>
                </div>
            </div>
            </div>
        </div>
    </footer>

    {{-- Animation --}}
    @include('layouts.includes.animation')
</body>
</html>
