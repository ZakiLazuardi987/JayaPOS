<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk – JAYA POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red: #C43626;
            --red-dark: #A93226;
            --primary-400: #F28377; /* Warna dropdown outlet baru */
            --bg: #EFF1F3;
            --white: #FFFFFF;
            --text: #1A1A1A;
            --border: #D1D5DB;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }

        /* Back Button Fixed */
        .back-btn {
            position: fixed; top: 32px; left: 32px;
            background: none; border: none; cursor: pointer; z-index: 50;
            transition: transform 0.2s; display: block;
        }
        .back-btn:hover { transform: translateX(-4px); }

        .container { width: 100%; max-width: 400px; padding: 24px; position: relative; }

        .logo-wrap { text-align: center; margin-bottom: 24px; }
        .instruction { text-align: center; font-size: 0.9rem; color: #4B5563; margin-bottom: 24px; }
        .user-email-display { text-align: center; font-weight: 700; font-size: 0.95rem; color: var(--text); margin-bottom: 8px; }

        .form-control {
            width: 100%; padding: 14px 16px; font-family: inherit; font-size: 0.95rem;
            border: 1.5px solid var(--border); border-radius: 8px; outline: none; transition: 0.2s;
            margin-bottom: 16px; background: var(--white);
        }
        .form-control:focus { border-color: var(--red); box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1); }
        .error-msg { color: var(--red); font-size: 0.85rem; margin-top: -10px; margin-bottom: 16px; display: none; text-align: center;}

        .btn-primary {
            width: 100%; padding: 14px; background: var(--red); color: #fff;
            font-size: 1rem; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; transition: 0.2s;
        }
        .btn-primary:hover { background: var(--red-dark); }

        /* Khusus Dropdown Outlet */
        .select-outlet {
            background-color: var(--primary-400); color: var(--white);
            text-align: center; font-weight: 600; border: none;
            appearance: none; -webkit-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat; background-position-x: 95%; background-position-y: 50%;
        }
        .select-outlet option { background: var(--white); color: var(--text); }

        #loadingOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4); display: none; align-items: center; justify-content: center; z-index: 999;
        }
        .loading-card {
            background: var(--white); padding: 24px 32px; border-radius: 8px;
            display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 90%; max-width: 300px; 
        }
        .spinner {
            width: 30px; height: 30px; border: 4px solid #E5E7EB;
            border-top: 4px solid var(--red); border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loading-text { font-weight: 600; font-size: 1rem; color: var(--text); }
        
        /* Toast Notification */
        .toast-container {
            position: fixed; top: -100px; left: 50%; transform: translateX(-50%);
            background: var(--red); color: white; padding: 12px 24px;
            border-radius: 8px; font-weight: 500; font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(196, 54, 38, 0.3); z-index: 1000;
            transition: top 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center; white-space: nowrap;
        }
        .toast-container.show { top: 32px; }
    </style>
</head>
<body>

    <div id="toast" class="toast-container">Pesan Error</div>

    <div id="loadingOverlay">
        <div class="loading-card">
            <div class="spinner"></div>
            <div class="loading-text">Memproses...</div>
        </div>
    </div>

    <button type="button" class="back-btn" id="btnBack">
        <img src="{{ asset('assets/back-arrow.png') }}" width="60" alt="Kembali">
    </button>

    <div class="container">
        
        <div class="logo-wrap" id="mainLogo">
            <img src="{{ asset('assets/jaya_text.png') }}" alt="JAYA Logo" style="height: 110px; object-fit: contain;">
        </div>

        <div id="step1">
            <p class="instruction">Masuk dengan email atau nomor handphone</p>
            
            <input type="text" id="identifier" class="form-control" placeholder="Email atau Nomor HP" required autofocus>
            <div id="errorMsg1" class="error-msg"></div>

            <button type="button" id="btnNext" class="btn-primary">Selanjutnya</button>
        </div>

        <div id="step2" style="display: none;">
            <p class="user-email-display" id="displayUserEmail"></p>
            <p class="instruction">Masukkan kata sandi Anda</p>
            
            <form id="formPassword">
                <input type="hidden" id="finalIdentifier">
                
                <div style="position: relative;">
                    <input type="password" id="password" class="form-control" placeholder="Kata Sandi" required>
                    <span id="togglePassword" style="position: absolute; right: 16px; top: 14px; color: #6B7280; cursor: pointer;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </span>
                </div>
                
                <div id="errorMsg2" class="error-msg"></div>
                <button type="submit" class="btn-primary">Masuk</button>
            </form>
        </div>

        <div id="step3" style="display: none;">
            <div class="logo-wrap" style="margin-bottom: 32px;">
                <img src="{{ asset('assets/jaya_square.png') }}" alt="JAYA Logo Square" style="width: 160px; height: 160px; border-radius: 16px; object-fit: contain;">
            </div>
            
            <form id="formOutlet">
                <select id="outletSelect" class="form-control select-outlet" required>
                    </select>
                <button type="submit" class="btn-primary" style="margin-top: 8px;">Masuk</button>
            </form>
        </div>

    </div>

    <script>
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const mainLogo = document.getElementById('mainLogo');
        const btnNext = document.getElementById('btnNext');
        const btnBack = document.getElementById('btnBack');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const identifierInput = document.getElementById('identifier');
        const finalIdentifier = document.getElementById('finalIdentifier');
        const passwordInput = document.getElementById('password');
        const outletSelect = document.getElementById('outletSelect');
        const errorMsg1 = document.getElementById('errorMsg1');
        const errorMsg2 = document.getElementById('errorMsg2');
        const toast = document.getElementById('toast');

        function showToast(msg) {
            toast.innerText = msg;
            toast.classList.add('show');
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        // Toggle Password
        document.getElementById('togglePassword').addEventListener('click', function () {
            passwordInput.setAttribute('type', passwordInput.getAttribute('type') === 'password' ? 'text' : 'password');
        });

        // Trigger Enter Step 1
        identifierInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") { e.preventDefault(); btnNext.click(); }
        });

        // -------- SUBMIT STEP 1 (Check Account) --------
        btnNext.addEventListener('click', async function() {
            const inputValue = identifierInput.value.trim();
            if (!inputValue) { errorMsg1.innerText = "Kolom tidak boleh kosong."; errorMsg1.style.display = "block"; return; }

            errorMsg1.style.display = "none";
            loadingOverlay.style.display = "flex"; 

            try {
                const response = await fetch("{{ route('login.check') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ identifier: inputValue })
                });

                const data = await response.json();
                setTimeout(() => {
                    loadingOverlay.style.display = "none";
                    if (response.ok) {
                        document.getElementById('displayUserEmail').innerText = data.identifier; 
                        finalIdentifier.value = data.identifier; 
                        step1.style.display = "none";
                        step2.style.display = "block";
                        passwordInput.focus();
                    } else {
                        errorMsg1.innerText = data.message;
                        errorMsg1.style.display = "block";
                    }
                }, 500);
            } catch (err) {
                loadingOverlay.style.display = "none";
                errorMsg1.innerText = "Terjadi kesalahan sistem."; errorMsg1.style.display = "block";
            }
        });

        // -------- SUBMIT STEP 2 (Verify Password via AJAX) --------
        document.getElementById('formPassword').addEventListener('submit', async function(e) {
            e.preventDefault();
            errorMsg2.style.display = "none";
            // Pastikan teks kembali ke "Memproses..."
            document.querySelector('.loading-text').innerText = "Memproses..."; 
            loadingOverlay.style.display = "flex";

            try {
                const response = await fetch("{{ route('login.verify') }}", {
                    method: "POST",
                    headers: { 
                        "Content-Type": "application/json", 
                        "Accept": "application/json", // Bagus untuk antisipasi redirect
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({ identifier: finalIdentifier.value, password: passwordInput.value })
                });

                const data = await response.json();
                setTimeout(() => {
                    loadingOverlay.style.display = "none";
                    if (response.ok) {
                        // UPDATE TOKEN CSRF DISINI (Kunci utamanya)
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.new_csrf);
                        // Susun isi Dropdown Outlet
                        outletSelect.innerHTML = '<option value="" disabled selected>Select Outlet</option>';
                        data.outlets.forEach(outlet => {
                            let selected = (outlet.outlet_id == data.user_outlet_id) ? 'selected' : '';
                            outletSelect.innerHTML += `<option value="${outlet.outlet_id}" ${selected}>${outlet.name}</option>`;
                        });

                        // Pindah ke Step 3
                        step2.style.display = "none";
                        mainLogo.style.display = "none"; // Hilangkan logo utama karena ada logo persegi
                        btnBack.style.display = "none"; // Sembunyikan panah back
                        step3.style.display = "block";
                    } else {
                        errorMsg2.innerText = data.message;
                        errorMsg2.style.display = "block";
                    }
                }, 500);
            } catch (err) {
                loadingOverlay.style.display = "none";
                errorMsg2.innerText = "Terjadi kesalahan sistem."; errorMsg2.style.display = "block";
            }
        });

        // -------- SUBMIT STEP 3 (Select Outlet & Redirect) --------
        document.getElementById('formOutlet').addEventListener('submit', async function(e) {
            e.preventDefault();
            loadingOverlay.style.display = "flex";

            try {
                const response = await fetch("{{ route('login.outlet') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ outlet_id: outletSelect.value })
                });
                
                const data = await response.json();
                if (response.ok) {
                    window.location.href = data.redirect; // Meluncur ke /pos
                } else {
                    loadingOverlay.style.display = "none";
                    showToast(data.message || 'Gagal memilih outlet.');
                }
            } catch (err) {
                loadingOverlay.style.display = "none";
                showToast('Terjadi kesalahan sistem saat memilih outlet.');
            }
        });

        // -------- FUNGSI BACK BUTTON --------
        btnBack.addEventListener('click', function(e) {
            e.preventDefault();
            if (step1.style.display === 'none' && step3.style.display === 'none') {
                step2.style.display = 'none';
                step1.style.display = 'block';
                passwordInput.value = ''; 
                errorMsg2.style.display = "none";
            } else {
                window.location.href = "{{ route('home') }}"; 
            }
        });
    </script>
</body>
</html>