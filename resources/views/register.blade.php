<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar – JAYA POS</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/jaya_square.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

    <style>
        /* Memastikan plugin tampil memenuhi lebar container (Full Width) */
        .iti { width: 100%; display: block; }
        .iti__flag {background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/img/flags.png");}
        @media (min-resolution: 2x) {
          .iti__flag {background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/img/flags@2x.png");}
        }
    </style>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:      #C43626;
            --red-dark: #A93226;
            --bg:       #EFF1F3;
            --white:    #FFFFFF;
            --text:     #1A1A1A;
            --muted:    #6B7280;
            --border:   #D1D5DB;
            --link:     #2563EB;
            --radius:   10px;
            --error:    #DC2626;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        /* ── Card ── */
        .card {
            background: var(--white);
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            padding: 40px 36px 32px;
            width: 100%;
            max-width: 420px;
        }

        /* ── Logo ── */
        .logo-wrap {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-svg {
            display: inline-block;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 13px 16px;
            font-family: inherit;
            font-size: .9rem;
            color: var(--text);
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control::placeholder { color: #9CA3AF; }

        .form-control:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(192, 57, 43, .12);
        }

        /* Error state */
        .form-control.is-invalid { border-color: var(--error); }
        .invalid-feedback {
            display: none;
            font-size: .78rem;
            color: var(--error);
            margin-top: 5px;
            padding-left: 4px;
        }
        .form-control.is-invalid ~ .invalid-feedback { display: block; }

        /* Laravel validation errors */
        .form-control.is-invalid-server { border-color: var(--error); }
        .server-error {
            font-size: .78rem;
            color: var(--error);
            margin-top: 5px;
            padding-left: 4px;
        }

        /* ── Phone row ── */
        .phone-row {
            display: flex;
            gap: 0;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
        }

        .phone-row:focus-within {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(192, 57, 43, .12);
        }

        .phone-prefix {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 0 12px;
            background: #F9FAFB;
            border-right: 1.5px solid var(--border);
            font-size: .88rem;
            color: var(--text);
            white-space: nowrap;
            font-weight: 600;
            flex-shrink: 0;
        }

        .flag {
            font-size: 1.1rem;
            line-height: 1;
        }

        .phone-input {
            flex: 1;
            padding: 13px 12px;
            font-family: inherit;
            font-size: .9rem;
            color: var(--text);
            border: none;
            outline: none;
            background: transparent;
        }

        .phone-input::placeholder { color: #9CA3AF; }

        /* ── Password toggle ── */
        .password-wrap { position: relative; }

        .password-wrap .form-control { padding-right: 44px; }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color .2s;
        }
        .toggle-password:hover { color: var(--muted); }

        /* ── Submit button ── */
        .btn-submit {
            display: block;
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            background: var(--red);
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            letter-spacing: .01em;
            transition: background .2s, transform .15s;
        }
        .btn-submit:hover { background: var(--red-dark); }
        .btn-submit:active { transform: translateY(0); }

        /* ── Sign in link ── */
        .sign-in {
            text-align: center;
            margin-top: 20px;
            font-size: .85rem;
            color: var(--muted);
        }
        .sign-in a {
            color: var(--link);
            text-decoration: none;
            font-weight: 600;
        }
        .sign-in a:hover { text-decoration: underline; }

        /* ── Alert errors ── */
        .alert-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: var(--radius);
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: .85rem;
            color: var(--error);
        }
        .alert-error ul { padding-left: 16px; }
        .alert-error li { margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="card">

        <!-- Logo -->
        <div class="logo-wrap">
            <div class="logo-wrap">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('assets/jaya_text.png') }}" alt="JAYA POS Logo" style="height: 50px; object-fit: contain;">
                </a>
            </div>
        </div>

        {{-- Global validation errors --}}
        @if ($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
            @csrf

            {{-- Nama --}}
            <div class="form-group">
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control {{ $errors->has('name') ? 'is-invalid-server' : '' }}"
                    placeholder="Nama seperti tertera di KTP"
                    value="{{ old('name') }}"
                    required
                    autocomplete="name"
                >
                @error('name')
                    <div class="server-error">{{ $message }}</div>
                @enderror
                <div class="invalid-feedback">Nama wajib diisi.</div>
            </div>

            {{-- Nomor HP --}}
            {{-- <div class="form-group">
                <div class="phone-row {{ $errors->has('phone') ? 'is-invalid-server' : '' }}">
                    <span class="phone-prefix">
                        <span class="flag">🇮🇩</span>
                        +62
                    </span>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        class="phone-input"
                        placeholder="812 xxxx xxxx"
                        value="{{ old('phone') }}"
                        required
                        autocomplete="tel"
                    >
                </div>
                @error('phone')
                    <div class="server-error">{{ $message }}</div>
                @enderror
                <div class="invalid-feedback" id="phoneError">Nomor HP wajib diisi.</div>
            </div> --}}
            <div class="form-group">
                <input type="hidden" name="phone" id="hidden_phone" value="{{ old('phone') }}">
                
                <input
                    type="tel"
                    id="phone_display"
                    class="form-control {{ $errors->has('phone') ? 'is-invalid-server' : '' }}"
                    style="padding-left: 50px;" placeholder="812 xxxx xxxx"
                    required
                >
                @error('phone')
                    <div class="server-error">{{ $message }}</div>
                @enderror
                <div class="invalid-feedback" id="phoneError">Nomor HP wajib diisi.</div>
            </div>

            {{-- Email --}}
            <div class="form-group">
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid-server' : '' }}"
                    placeholder="Email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                >
                @error('email')
                    <div class="server-error">{{ $message }}</div>
                @enderror
                <div class="invalid-feedback">Email wajib diisi dengan format yang benar.</div>
            </div>

            {{-- Kata Sandi --}}
            <div class="form-group password-wrap">
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid-server' : '' }}"
                    placeholder="Kata Sandi"
                    required
                    autocomplete="new-password"
                >
                <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan/sembunyikan kata sandi">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                        <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
                @error('password')
                    <div class="server-error">{{ $message }}</div>
                @enderror
                <div class="invalid-feedback">Kata sandi wajib diisi (minimal 8 karakter).</div>
            </div>

            {{-- Hidden: confirm password (same value) - atau bisa tambahkan field confirm terpisah --}}
            <input type="hidden" name="password_confirmation" id="password_confirmation">

            <button type="submit" class="btn-submit">Daftar</button>
            <script>
                // 1. Inisialisasi Plugin intl-tel-input
                const phoneInputField = document.querySelector("#phone_display");
                const hiddenPhoneInput = document.querySelector("#hidden_phone");
                
                const phoneInput = window.intlTelInput(phoneInputField, {
                    initialCountry: "id", // Default Indonesia
                    preferredCountries: ["id", "my", "sg", "th"], // Negara yang muncul di paling atas
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
                });
        
                // 2. Modifikasi event submit form yang sudah kamu buat sebelumnya
                document.getElementById('registerForm').addEventListener('submit', function(e) {
                    let valid = true;
                    // ... (Kode validasi nama dan email milikmu biarkan saja di sini) ...
        
                    // Menyalin nomor utuh beserta kode negara ke input tersembunyi (hidden_phone)
                    if (phoneInputField.value.trim()) {
                        hiddenPhoneInput.value = phoneInput.getNumber(); // getNumber() akan otomatis mengembalikan format +62...
                    }
        
                    // Validasi HP kosong
                    if (!phoneInputField.value.trim()) {
                        phoneInputField.classList.add('is-invalid');
                        document.getElementById('phoneError').style.display = 'block';
                        valid = false;
                    } else {
                        phoneInputField.classList.remove('is-invalid');
                        document.getElementById('phoneError').style.display = 'none';
                    }
        
                    if (!valid) e.preventDefault();
                });
            </script>
        </form>

        <p class="sign-in">
            Already have a JAYA POS account? <a href="{{ route('login') }}">Sign in</a>
        </p>
    </div>

    <script>
        // Toggle password visibility
        const toggleBtn  = document.getElementById('togglePassword');
        const pwdInput   = document.getElementById('password');
        const eyeIcon    = document.getElementById('eyeIcon');

        const eyeOpen = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
        const eyeOff  = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;

        toggleBtn.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type  = isHidden ? 'text' : 'password';
            eyeIcon.innerHTML = isHidden ? eyeOpen : eyeOff;
        });

        // Sync password_confirmation
        pwdInput.addEventListener('input', () => {
            document.getElementById('password_confirmation').value = pwdInput.value;
        });

        // Client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let valid = true;

            const name  = document.getElementById('name');
            const phone = document.getElementById('phone');
            const email = document.getElementById('email');
            const pwd   = document.getElementById('password');

            // Name
            if (!name.value.trim()) {
                name.classList.add('is-invalid'); valid = false;
            } else {
                name.classList.remove('is-invalid');
            }

            // Phone
            const phoneRow = phone.closest('.phone-row');
            if (!phone.value.trim()) {
                phoneRow.style.borderColor = '#DC2626';
                document.getElementById('phoneError').style.display = 'block';
                valid = false;
            } else {
                phoneRow.style.borderColor = '';
                document.getElementById('phoneError').style.display = 'none';
            }

            // Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email.value.trim() || !emailRegex.test(email.value)) {
                email.classList.add('is-invalid'); valid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            // Password
            if (!pwd.value || pwd.value.length < 8) {
                pwd.classList.add('is-invalid'); valid = false;
            } else {
                pwd.classList.remove('is-invalid');
            }

            if (!valid) e.preventDefault();
        });
    </script>
</body>
</html>