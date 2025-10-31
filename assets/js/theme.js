// assets/js/theme.js - Fungsi untuk toggle tema gelap/terang

// Fungsi untuk mengecek preferensi tema pengguna
function getUserThemePreference() {
    // Cek apakah pengguna sudah login
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn()) {
        // Jika pengguna login, ambil preferensi dari server
        // Untuk sementara, kita gunakan localStorage
        return localStorage.getItem('theme-mode') || 'light';
    } else {
        // Jika tidak login, gunakan localStorage
        return localStorage.getItem('theme-mode') || 'light';
    }
}

// Fungsi untuk menerapkan tema
function applyTheme(themeMode) {
    if (themeMode === 'dark') {
        document.body.classList.add('dark-mode');
        document.body.classList.remove('light-mode');
    } else {
        document.body.classList.add('light-mode');
        document.body.classList.remove('dark-mode');
    }
    
    // Update ikon toggle
    updateThemeToggleIcon();
}

// Fungsi untuk toggle tema
function toggleTheme() {
    const currentTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
    applyTheme(currentTheme);
    
    // Simpan preferensi ke localStorage
    localStorage.setItem('theme-mode', currentTheme);
    
    // Jika pengguna login, simpan preferensi ke server
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn()) {
        saveUserThemePreference(currentTheme);
    }
}

// Fungsi untuk memperbarui ikon toggle
function updateThemeToggleIcon() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        if (document.body.classList.contains('dark-mode')) {
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            themeToggle.title = 'Ganti ke mode terang';
        } else {
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            themeToggle.title = 'Ganti ke mode gelap';
        }
    }
}

// Fungsi untuk menyimpan preferensi tema pengguna ke server
function saveUserThemePreference(themeMode) {
    // Dalam implementasi sebenarnya, ini akan mengirim data ke server
    // Contoh:
    /*
    fetch('server/api/user_theme.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            theme_mode: themeMode
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Preferensi tema disimpan:', data);
    })
    .catch(error => {
        console.error('Error menyimpan preferensi tema:', error);
    });
    */
}

// Fungsi untuk otomatisasi tema berdasarkan waktu
function autoSetThemeByTime() {
    const hour = new Date().getHours();
    // Gunakan tema gelap antara pukul 18:00 - 06:00
    const isNightTime = hour >= 18 || hour < 6;
    
    // Hanya otomatisasi jika pengguna belum menetapkan preferensi
    if (!localStorage.getItem('theme-mode')) {
        const autoTheme = isNightTime ? 'dark' : 'light';
        applyTheme(autoTheme);
        localStorage.setItem('theme-mode', autoTheme);
    }
}

// Fungsi untuk menambahkan tombol toggle tema ke halaman
function addThemeToggle() {
    // Periksa apakah tombol toggle sudah ada
    if (document.getElementById('theme-toggle')) {
        return;
    }
    
    // Buat elemen tombol toggle
    const themeToggle = document.createElement('div');
    themeToggle.id = 'theme-toggle';
    themeToggle.className = 'theme-toggle-btn';
    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    themeToggle.title = 'Ganti ke mode gelap';
    themeToggle.onclick = toggleTheme;
    
    // Tambahkan ke header jika ada
    const header = document.querySelector('.header') || document.querySelector('header');
    if (header) {
        header.appendChild(themeToggle);
    } else {
        // Jika tidak ada header, tambahkan ke body
        document.body.appendChild(themeToggle);
    }
    
    // Tambahkan CSS untuk tombol toggle
    addThemeToggleCSS();
}

// Fungsi untuk menambahkan CSS tombol toggle
function addThemeToggleCSS() {
    if (document.getElementById('theme-toggle-styles')) {
        return;
    }
    
    const style = document.createElement('style');
    style.id = 'theme-toggle-styles';
    style.textContent = `
        .theme-toggle-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .theme-toggle-btn:hover {
            transform: scale(1.1);
            background: linear-gradient(to right, #0a3d62, #145a82);
        }
    `;
    document.head.appendChild(style);
}

// Fungsi untuk menambahkan CSS tema gelap
function addDarkThemeCSS() {
    if (document.getElementById('dark-theme-styles')) {
        return;
    }
    
    const style = document.createElement('style');
    style.id = 'dark-theme-styles';
    style.textContent = `
        .dark-mode {
            background-color: #1a1a2e !important;
            color: #e6e6e6 !important;
        }
        
        .dark-mode .header,
        .dark-mode .content,
        .dark-mode .question-box,
        .dark-mode .answer-box,
        .dark-mode .intro-box,
        .dark-mode .daily-fact-section {
            background-color: #16213e !important;
            color: #e6e6e6 !important;
        }
        
        .dark-mode h1,
        .dark-mode h2,
        .dark-mode h3,
        .dark-mode h4,
        .dark-mode h5,
        .dark-mode h6 {
            color: #f1c40f !important;
        }
        
        .dark-mode .btn-start,
        .dark-mode .btn-submit,
        .dark-mode .btn-secondary {
            background: linear-gradient(to right, #0a3d62, #145a82) !important;
            color: white !important;
            border: 1px solid #f1c40f !important;
        }
        
        .dark-mode .btn-start:hover,
        .dark-mode .btn-submit:hover,
        .dark-mode .btn-secondary:hover {
            background: linear-gradient(to right, #001f3f, #0a3d62) !important;
        }
        
        .dark-mode .tag {
            background-color: #0f3460 !important;
            color: #e6e6e6 !important;
        }
        
        .dark-mode .tag:hover {
            background-color: #1a1a2e !important;
        }
        
        .dark-mode .ai-response,
        .dark-mode .quran-verse,
        .dark-mode .question-display {
            background-color: #0f3460 !important;
            color: #e6e6e6 !important;
        }
        
        .dark-mode .form-group input {
            background-color: #0f3460 !important;
            color: #e6e6e6 !important;
            border: 1px solid #4cc9f0 !important;
        }
        
        .dark-mode .feature {
            background-color: #0f3460 !important;
        }
        
        .dark-mode footer {
            background-color: #0f3460 !important;
            color: #e6e6e6 !important;
        }
        
        .dark-mode .feature-highlights {
            background-color: #0f3460 !important;
        }
    `;
    document.head.appendChild(style);
}

// Inisialisasi tema saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan CSS tema gelap
    addDarkThemeCSS();
    
    // Tambahkan tombol toggle tema
    addThemeToggle();
    
    // Deteksi dan terapkan tema preferensi pengguna
    const userTheme = getUserThemePreference();
    applyTheme(userTheme);
    
    // Atur tema otomatis berdasarkan waktu jika belum ada preferensi
    autoSetThemeByTime();
});