// Main JavaScript file for Science-Qur'an Integration
// Handles navigation and core functionality

// Fungsi untuk toggle tema gelap/terang
function toggleTheme() {
    const body = document.body;
    if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        localStorage.setItem('theme-mode', 'light');
    } else {
        body.classList.add('dark-mode');
        localStorage.setItem('theme-mode', 'dark');
    }
    updateThemeIcon();
}

// Fungsi untuk memperbarui ikon toggle tema
function updateThemeIcon() {
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
    
    // Tambahkan elemen ke body
    document.body.appendChild(themeToggle);
}

// Fungsi untuk menerapkan tema yang disimpan
function applySavedTheme() {
    const savedTheme = localStorage.getItem('theme-mode');
    const body = document.body;
    
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    } else {
        body.classList.remove('dark-mode');
    }
    
    // Update ikon sesuai dengan tema
    setTimeout(updateThemeIcon, 100); // Delay kecil agar kelas sudah diterapkan
}

// Fungsi untuk mengecek apakah pengguna sudah login
function isLoggedIn() {
    // Dalam implementasi sebenarnya, Anda akan memeriksa sesi pengguna
    // Untuk sementara, kita hanya memeriksa apakah ada indikator login di sessionStorage
    return sessionStorage.getItem('isLoggedIn') === 'true' || 
           document.querySelector('.auth-buttons') !== null;
}

// Tambahkan CSS untuk tombol toggle tema
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
            right: 70px;
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

function startExploration() {
    // Navigate to question page after clicking "Mulai Eksplorasi"
    // Validate the destination to prevent open redirect vulnerability
    window.location.href = 'question.html';
}

// Function to handle page transitions with animations
function animatePageTransition() {
    document.body.style.opacity = 0;
    setTimeout(function() {
        document.body.style.opacity = 1;
    }, 100);
}

// Check if user is on opening page and add special effects
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan CSS tombol toggle
    addThemeToggleCSS();
    
    // Tambahkan tombol toggle tema
    addThemeToggle();
    
    // Terapkan tema yang disimpan
    applySavedTheme();
    
    // Add animation to feature highlights when on opening page
    if (window.location.pathname.includes('opening.html')) {
        // Add animation to feature highlights when page loads
        const features = document.querySelectorAll('.feature');
        features.forEach((feature, index) => {
            setTimeout(() => {
                feature.style.opacity = '0';
                feature.style.transform = 'translateY(20px)';
                feature.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    feature.style.opacity = '1';
                    feature.style.transform = 'translateY(0)';
                }, index * 200);
            }, 500);
        });
    }
    
    // Add loading animation for other pages
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.opacity = '1';
        document.body.style.transition = 'opacity 0.3s ease';
    }, 50);
});