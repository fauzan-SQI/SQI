// JavaScript for answer page functionality

function displayAnswer() {
    // Get the question from localStorage
    let question = localStorage.getItem('currentQuestion') || 'Contoh: Apa yang dikatakan Al-Qur\'an tentang penciptaan alam semesta?';
    
    // Sanitize the question to prevent XSS
    question = sanitizeInput(question);
    
    // Display the question
    document.getElementById('displayed-question').textContent = question;
    
    // Fetch answer from the backend API
    fetchAnswerFromAPI(question);
}

function fetchAnswerFromAPI(question) {
    // Sanitize the question before using it
    question = sanitizeInput(question);
    
    // Show loading state
    document.getElementById('scientific-answer').textContent = 'Memproses pertanyaan Anda...';
    
    // Try Node.js backend first, then fall back to PHP
    const nodeEndpoint = `/api/getAnswer?question=${encodeURIComponent(question)}`;
    const phpEndpoint = `server/getAnswer.php?question=${encodeURIComponent(question)}`;
    
    // Attempt to fetch from Node.js server first
    fetch(nodeEndpoint)
        .then(response => {
            if (!response.ok) {
                throw new Error('Node.js endpoint not available');
            }
            return response.json();
        })
        .then(data => {
            if (data && !data.error) {
                displayAnswerData(data);
            } else {
                // If Node.js fails, try PHP endpoint
                return tryPhpEndpoint(question);
            }
        })
        .catch(error => {
            console.log('Node.js endpoint not available, trying PHP:', error.message);
            // Try PHP endpoint if Node.js fails
            return tryPhpEndpoint(question);
        });
}

function tryPhpEndpoint(question) {
    // Sanitize the question before using it
    question = sanitizeInput(question);
    
    const phpEndpoint = `server/getAnswer.php?question=${encodeURIComponent(question)}`;
    
    fetch(phpEndpoint)
        .then(response => response.json())
        .then(data => {
            if (data && !data.error) {
                displayAnswerData(data);
            } else {
                console.error('Error from PHP endpoint:', data.error || 'Unknown error');
                // Display default answer if both endpoints fail
                displayDefaultAnswer(question);
            }
        })
        .catch(error => {
            console.error('Error connecting to backend:', error);
            // Display default answer if both endpoints fail
            displayDefaultAnswer(question);
        });
}

function displayAnswerData(data) {
    // Sanitize all data before using it
    const questionKeywords = sanitizeInput(data.question_keywords || 'Pertanyaan Anda');
    let answerText = sanitizeInput(data.answer_text || 'Penjelasan ilmiah akan ditampilkan di sini.');
    
    // Update the displayed content with actual data from backend
    document.getElementById('displayed-question').textContent = questionKeywords;
    
    // Format the answer text with basic markdown-like formatting for AI responses
    const formattedAnswer = formatResponseText(answerText);
    document.getElementById('scientific-answer').innerHTML = formattedAnswer;
    
    // Update Quran verse
    const quranRefDiv = document.getElementById('quran-reference');
    if (data.quran_reference) {
        // Sanitize quran_reference
        const quranReference = sanitizeInput(data.quran_reference);
        
        // Split the data assuming format: "Reference - Arabic text" or similar
        const parts = quranReference.split(' - ');
        if (parts.length > 1) {
            // If we have both reference and text
            quranRefDiv.innerHTML = `
                <p class="arabic">???? ??????? ???????? ???? ?????????? ????? ?????? ??????? ??????? ???????? ?????? ????? ??????????</p>
                <p class="translation">Terjemahan ayat akan ditampilkan di sini.</p>
                <p class="reference">${escapeHtml(parts[0])}</p>
            `;
        } else {
            // If only one part, display as reference
            quranRefDiv.innerHTML = `
                <p class="arabic">???? ??????? ???????? ???? ?????????? ????? ?????? ??????? ??????? ???????? ?????? ????? ??????????</p>
                <p class="translation">Terjemahan ayat akan ditampilkan di sini.</p>
                <p class="reference">Referensi ayat</p>
            `;
        }
    } else {
        // If no Quran reference was provided, try to extract one from the response
        if (data.answer_text) {
            const extractedReference = extractQuranReference(data.answer_text);
            if (extractedReference) {
                quranRefDiv.innerHTML = extractedReference;
            }
        }
    }
    
    // Update video - display as text link instead of embedding
    const videoContainer = document.getElementById('video-container');
    
    // Check if we have a video reference (new approach) or youtube_link (old approach)
    if (data.video_reference || data.youtube_link) {
        const videoUrl = data.video_reference || data.youtube_link;
        
        // Validate the YouTube URL
        let videoId = getYouTubeVideoId(videoUrl);
        if (videoId) {
            // Display video as a text link with icon instead of embedding
            videoContainer.innerHTML = `
                <div class="video-link-container">
                    <p><i class="fas fa-external-link-alt"></i> <strong>Video Referensi:</strong></p>
                    <p>Anda dapat menonton video penjelasan terkait di tautan berikut:</p>
                    <p><a href="https://www.youtube.com/watch?v=${escapeHtml(videoId)}" target="_blank" class="video-link">
                        <i class="fab fa-youtube"></i> Tonton Video di YouTube
                    </a></p>
                    <p class="video-note">Video ini memberikan penjelasan visual tambahan tentang topik yang dibahas.</p>
                </div>
            `;
        } else {
            // If URL is not valid, show a generic message
            videoContainer.innerHTML = `
                <div class="video-link-container">
                    <p><i class="fas fa-info-circle"></i> <strong>Informasi Video:</strong></p>
                    <p>Video penjelasan tersedia untuk topik ini. Silakan kunjungi platform video kami untuk melihat konten visual terkait.</p>
                </div>
            `;
        }
    } else {
        // If no video is available, show a message
        videoContainer.innerHTML = `
            <div class="video-link-container">
                <p><i class="fas fa-info-circle"></i> <strong>Informasi Video:</strong></p>
                <p>Saat ini tidak tersedia video penjelasan untuk topik ini. Silakan periksa kembali nanti untuk konten multimedia tambahan.</p>
            </div>
        `;
    }
    
    // Add any additional styling to the response
    document.getElementById('scientific-answer').classList.add('formatted-response');
}

function getYouTubeVideoId(url) {
    // Validate if the URL is a proper YouTube URL
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    
    if (match && match[2].length === 11) {
        // Additional validation: ensure the video ID contains only valid characters
        if (/^[a-zA-Z0-9_-]{11}$/.test(match[2])) {
            return match[2];
        }
    }
    return null;
}

function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') return unsafe;
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function sanitizeInput(input) {
    if (typeof input !== 'string') return input;
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
}

function formatResponseText(text) {
    if (typeof text !== 'string' || text.trim() === '') {
        return '<p>Penjelasan ilmiah akan ditampilkan di sini.</p>';
    }

    const paragraphs = text.split(/\n{2,}/).map(segment => {
        let formattedSegment = segment
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/`(.+?)`/g, '<code>$1</code>')
            .replace(/~~(.+?)~~/g, '<del>$1</del>');

        formattedSegment = formattedSegment.replace(/\n/g, '<br>');
        return `<p>${formattedSegment}</p>`;
    });

    return paragraphs.join('') || `<p>${text}</p>`;
}

function extractQuranReference(rawText) {
    if (typeof rawText !== 'string') {
        return '';
    }

    const referenceRegex = /(QS\.?\s*[A-Za-z0-9'()\-\s]+:\s*\d+(?:-\d+)?)/i;
    const match = rawText.match(referenceRegex);

    if (!match) {
        return '';
    }

    const referenceText = escapeHtml(match[1]);

    return `
        <p class="arabic">Teks Arab ayat akan ditampilkan ketika tersedia.</p>
        <p class="translation">Silakan merujuk pada mushaf atau database Al-Qur'an untuk teks dan terjemahan lengkap.</p>
        <p class="reference">${referenceText}</p>
    `;
}

function displayDefaultAnswer(question) {
    // Sanitize the question to prevent XSS
    question = sanitizeInput(question);
    
    // Default answer when backend is not available
    document.getElementById('displayed-question').textContent = question;
    document.getElementById('scientific-answer').textContent = 'Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan mencocokkan kata kunci dari pertanyaan Anda dengan database yang berisi penjelasan ilmiah dan ayat Al-Qur\'an terkait.';
    
    // Update Quran verse
    const quranRefDiv = document.getElementById('quran-reference');
    quranRefDiv.innerHTML = `
        <p class="arabic">???? ??????? ???????? ???? ?????????? ????? ?????? ??????? ??????? ???????? ?????? ????? ??????????</p>
        <p class="translation">Dialah yang menurunkan air dari langit; sebagian menjadi minuman untukmu dan sebagian lagi menyirami tumbuhan yang menjadi tempat kalian menggembalakan ternak.</p>
        <p class="reference">QS. An-Nahl: 10</p>
    `;
    
    // Update video with a more informative message and retry mechanism
    const videoContainer = document.getElementById('video-container');
    videoContainer.innerHTML = `
        <div class="video-link-container">
            <p><i class="fas fa-video"></i> <strong>Video Penjelasan:</strong></p>
            <p>Video penjelasan tambahan akan ditampilkan di sini ketika backend tersedia.</p>
            <p>Sedang mencoba menghubungkan ke backend...</p>
            <div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
    `;
    
    // Retry connection to backend after a delay
    setTimeout(() => {
        const currentQuestion = localStorage.getItem('currentQuestion');
        if (currentQuestion) {
            const sanitizedQuestion = sanitizeInput(currentQuestion);
            fetchAnswerFromAPI(sanitizedQuestion);
        }
    }, 5000); // Retry after 5 seconds
}

function displayTafsir(tafsirData) {
    const tafsirSection = document.getElementById('tafsir-section');
    const tafsirContent = document.getElementById('tafsir-content');
    
    if (!tafsirData || tafsirData.length === 0) {
        tafsirSection.style.display = 'none';
        return;
    }
    
    // Kosongkan konten sebelumnya
    tafsirContent.innerHTML = '';
    
    // Tampilkan setiap item tafsir
    tafsirData.forEach(function(item) {
        if (item.tafsir && item.tafsir.length > 0) {
            const ayahRef = item.ayah ? 
                `<div class="ayah-reference">QS. ${item.ayah.surah_number}:${item.ayah.ayah_number}</div>` : '';
                
            item.tafsir.forEach(function(tafsir) {
                const tafsirItem = document.createElement('div');
                tafsirItem.className = 'tafsir-item';
                tafsirItem.innerHTML = `
                    <div class="tafsir-source">${escapeHtml(tafsir.tafsir_source)}</div>
                    <div class="tafsir-text">${escapeHtml(tafsir.tafsir_text)}</div>
                    ${ayahRef}
                `;
                tafsirContent.appendChild(tafsirItem);
            });
        }
    });
    
    // Tampilkan seksi tafsir
    tafsirSection.style.display = 'block';
}

function backToQuestion() {
    window.location.href = 'question.html';
}

document.addEventListener('DOMContentLoaded', displayAnswer);

