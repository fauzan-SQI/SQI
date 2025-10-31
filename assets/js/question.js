// JavaScript for question page functionality

function submitQuestion(event) {
    event.preventDefault();
    
    const questionInput = document.getElementById('user-question');
    let question = questionInput.value.trim();
    
    // Sanitize input to prevent XSS
    question = sanitizeInput(question);
    
    if (!question) {
        alert('Mohon masukkan pertanyaan Anda terlebih dahulu!');
        return;
    }
    
    // Additional validation to prevent overly long questions
    if (question.length > 500) {
        alert('Pertanyaan terlalu panjang. Mohon masukkan pertanyaan dengan maksimal 500 karakter.');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.btn-submit');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    submitBtn.disabled = true;
    
    // Store the question in localStorage for the answer page to access
    localStorage.setItem('currentQuestion', question);
    
    // In a real implementation, we would send the question to the backend
    // and handle the response appropriately
    // For now, we'll redirect after a short delay to simulate processing
    setTimeout(function() {
        window.location.href = 'answer.html';
    }, 1500);
}

function fillQuestion(questionText) {
    // Sanitize input to prevent XSS
    questionText = sanitizeInput(questionText);
    document.getElementById('user-question').value = questionText;
    // Scroll to the textarea to make it visible
    document.getElementById('user-question').focus();
}

// Sanitize input to prevent XSS
function sanitizeInput(input) {
    if (typeof input !== 'string') return input;
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

// Load any saved question from localStorage if available
document.addEventListener('DOMContentLoaded', function() {
    // Add placeholder animation
    const textarea = document.getElementById('user-question');
    if (textarea) {
        textarea.addEventListener('focus', function() {
            this.placeholder = '';
        });
        
        textarea.addEventListener('blur', function() {
            if (this.value === '') {
                this.placeholder = 'Contoh: Apa yang dikatakan Al-Qur\'an tentang penciptaan alam semesta?';
            }
        });
    }
    
    // Add animation to suggested questions
    const tags = document.querySelectorAll('.tag');
    tags.forEach((tag, index) => {
        tag.style.opacity = '0';
        tag.style.transform = 'translateY(10px)';
        tag.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        setTimeout(() => {
            tag.style.opacity = '1';
            tag.style.transform = 'translateY(0)';
        }, 300 + index * 100);
    });
    
    // Focus on the question input when page loads
    const questionInput = document.getElementById('user-question');
    if (questionInput) {
        questionInput.focus();
    }
});