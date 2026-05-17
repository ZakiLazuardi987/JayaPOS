<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JAYA POS – Selling Made Easy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:       #C43626;
            --red-dark:  #A93226;
            --red-light: #E74C3C;
            --bg:        #EFF1F3;
            --white:     #FFFFFF;
            --text:      #1A1A1A;
            --muted:     #6B7280;
            --link:      #2563EB;
            --radius:    12px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px 40px;
            gap: 28px;
        }

        /* ── Carousel Card ── */
        .carousel-wrap {
            width: 100%;
            max-width: 600px;
            position: relative;
        }

        .carousel-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 36px 40px 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            overflow: hidden;
        }

        /* nav arrows */
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-60%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            font-size: 22px;
            padding: 8px;
            transition: color .2s;
            z-index: 10;
        }
        .carousel-arrow:hover { color: var(--text); }
        .carousel-arrow.prev { left: -36px; }
        .carousel-arrow.next { right: -36px; }

        /* slides */
        .slides { overflow: hidden; }

        .slide {
            display: none;
            align-items: center;
            gap: 32px;
            min-height: 130px;
            animation: fadeIn .35s ease;
        }
        .slide.active { display: flex; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(16px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* PERUBAHAN DI SINI: Container diset jadi persegi 130x130px */
        .slide-illustration {
            flex: 0 0 130px;
            width: 130px;
            height: 130px; 
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* PERUBAHAN DI SINI: Class baru untuk menjaga aspect ratio gambar dari Figma */
        .slide-img-asset {
            width: 100%;
            height: 100%;
            object-fit: contain; 
        }

        .slide-text h2 {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .slide-text p {
            font-size: .875rem;
            color: var(--muted);
            line-height: 1.6;
        }

        /* dots */
        .dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }
        .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #D1D5DB;
            border: none;
            cursor: pointer;
            transition: background .2s;
            padding: 0;
        }
        .dot.active { background: #6B7280; }

        /* ── CTA ── */
        .cta-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            width: 100%;
            max-width: 360px;
        }

        .btn-primary {
            display: block;
            width: 100%;
            padding: 15px 24px;
            background: var(--red);
            color: #fff;
            font-family: inherit;
            font-size: .95rem;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background .2s, transform .15s;
            letter-spacing: .01em;
        }
        .btn-primary:hover { background: var(--red-dark); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }

        .sign-in-text {
            font-size: .85rem;
            color: var(--muted);
        }
        .sign-in-text a {
            color: var(--link);
            text-decoration: none;
            font-weight: 600;
        }
        .sign-in-text a:hover { text-decoration: underline; }

        /* ── Legal ── */
        .legal {
            font-size: .78rem;
            color: #9CA3AF;
            font-style: italic;
            text-align: center;
            max-width: 420px;
        }
        .legal a {
            color: var(--link);
            text-decoration: none;
            font-style: normal;
        }
        .legal a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    @if(session('success'))
        <div id="toast-success" style="position: fixed; top: 24px; right: 24px; background: #10B981; color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; font-weight: 600; display: flex; align-items: center; gap: 12px; animation: slideIn 0.3s ease-out;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            {{ session('success') }}
        </div>
        <style>
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        </style>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast-success');
                if(toast) {
                    toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 500); 
                }
            }, 5000);
        </script>
    @endif

    <div class="carousel-wrap">
        <button class="carousel-arrow prev" id="prevBtn" aria-label="Slide sebelumnya">&#8249;</button>

        <div class="carousel-card">
            <div class="slides">

                <div class="slide active">
                    <div class="slide-illustration">
                        <img src="{{ asset('assets/carousel_1.png') }}" alt="Selling made easy" class="slide-img-asset">
                    </div>
                    <div class="slide-text">
                        <h2>Selling made easy</h2>
                        <p>Grow your business effortlessly with the POS build for simplicity.</p>
                    </div>
                </div>

                <div class="slide">
                    <div class="slide-illustration">
                        <img src="{{ asset('assets/carousel_2.png') }}" alt="Set up your store" class="slide-img-asset">
                    </div>
                    <div class="slide-text">
                        <h2>Set up your store in no time at all</h2>
                        <p>Easily input items, assign prices, and manage your stock anywhere.</p>
                    </div>
                </div>

                <div class="slide">
                    <div class="slide-illustration">
                        <img src="{{ asset('assets/carousel_3.png') }}" alt="Breeze through checkouts" class="slide-img-asset">
                    </div>
                    <div class="slide-text">
                        <h2>Breeze through checkouts</h2>
                        <p>Take orders and charge your customers using a variety of mobile payments.</p>
                    </div>
                </div>

                <div class="slide">
                    <div class="slide-illustration">
                        <img src="{{ asset('assets/carousel_4.png') }}" alt="Real-time reports" class="slide-img-asset">
                    </div>
                    <div class="slide-text">
                        <h2>Manage your business with real-time reports</h2>
                        <p>Once you start selling, sign in to your Backoffice to gain insights into your business.</p>
                    </div>
                </div>

            </div><div class="dots" id="dots">
                <button class="dot active" data-index="0" aria-label="Slide 1"></button>
                <button class="dot" data-index="1" aria-label="Slide 2"></button>
                <button class="dot" data-index="2" aria-label="Slide 3"></button>
                <button class="dot" data-index="3" aria-label="Slide 4"></button>
            </div>
        </div><button class="carousel-arrow next" id="nextBtn" aria-label="Slide berikutnya">&#8250;</button>
    </div><div class="cta-section">
        <a href="{{ route('register') }}" class="btn-primary">Start Selling with JAYA POS</a>
        <p class="sign-in-text">
            Already have a JAYA POS account? <a href="{{ route('login') }}">Sign in</a>
        </p>
    </div>

    <p class="legal">
        <em>Dengan melakukan pendaftaran Anda telah menyetujui
        <a href="#">syarat</a> dan <a href="#">ketentuan</a> JAYA</em>
    </p>

    <script>
        const slides = document.querySelectorAll('.slide');
        const dots   = document.querySelectorAll('.dot');
        let current  = 0;

        function goTo(index) {
            slides[current].classList.remove('active');
            dots[current].classList.remove('active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
        }

        document.getElementById('prevBtn').addEventListener('click', () => goTo(current - 1));
        document.getElementById('nextBtn').addEventListener('click', () => goTo(current + 1));
        dots.forEach(dot => dot.addEventListener('click', () => goTo(+dot.dataset.index)));

        // Auto-play every 4 s
        setInterval(() => goTo(current + 1), 5000);
    </script>
</body>
</html>