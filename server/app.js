// server/app.js - Server Node.js alternatif untuk Science-Qur'an Integration

const express = require('express');
const mysql = require('mysql2');
const path = require('path');
const cors = require('cors');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// Konfigurasi database
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'science_quran'
};

// Koneksi ke database
const pool = mysql.createPool(dbConfig).promise();

// Endpoint untuk mendapatkan jawaban
app.get('/api/getAnswer', async (req, res) => {
  try {
    const { question } = req.query;
    
    if (!question) {
      return res.status(400).json({ error: 'Question parameter is required' });
    }
    
    // Cari jawaban di database berdasarkan keyword
    const [rows] = await pool.execute(
      'SELECT * FROM answers WHERE question_keywords LIKE ? ORDER BY id DESC LIMIT 1',
      [`%${question}%`]
    );
    
    if (rows.length > 0) {
      // Jika ditemukan di database
      const answer = rows[0];
      return res.json({
        id: answer.id,
        question_keywords: answer.question_keywords,
        answer_text: answer.answer_text,
        quran_reference: answer.quran_reference,
        youtube_link: answer.youtube_link
      });
    } else {
      // Jika tidak ditemukan, panggil AI (ini hanya contoh, tidak benar-benar memanggil AI di sini)
      const defaultResponse = {
        id: 0,
        question_keywords: question,
        answer_text: 'Penjelasan ilmiah yang relevan dengan pertanyaan Anda akan ditampilkan di sini. Dalam versi lengkap aplikasi, sistem akan menghubungi layanan AI untuk menghasilkan jawaban.',
        quran_reference: 'QS. An-Nahl: 10 - هوَ الَّذِي أَنزَلَ مِنَ السَّمَاءِ مَاءً لَّكُم مِّنْهُ شَرَابٌ وَمِنْهُ شَجَرٌ فِيهِ تَسِيمُونَ',
        youtube_link: 'https://www.youtube.com/embed/abcd1234'
      };
      
      return res.json(defaultResponse);
    }
  } catch (error) {
    console.error('Error getting answer:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Endpoint untuk mendapatkan fakta harian
app.get('/api/daily_fact', async (req, res) => {
  try {
    // Ambil fakta untuk hari ini
    const today = new Date().toISOString().split('T')[0];
    
    let [rows] = await pool.execute(
      'SELECT fact_text, quran_reference FROM daily_facts WHERE created_at = ? AND is_active = 1',
      [today]
    );
    
    // Jika tidak ada fakta untuk hari ini, ambil acak
    if (rows.length === 0) {
      [rows] = await pool.execute(
        'SELECT fact_text, quran_reference FROM daily_facts WHERE is_active = 1 ORDER BY RAND() LIMIT 1'
      );
    }
    
    if (rows.length > 0) {
      res.json(rows[0]);
    } else {
      // Fallback jika tidak ada fakta sama sekali
      res.json({
        fact_text: 'Di dalam Al-Qur\'an terdapat banyak ayat yang menjelaskan fenomena alam dan sains. Aplikasi Science-Qur\'an Integration membantu Anda menemukan hubungan antara pengetahuan sains modern dan ayat-ayat Al-Qur\'an.',
        quran_reference: 'QS. Al-Mulk: 3 - Dan Dialah yang menciptakan langit dan bumi dalam seisinya, dan Kami melangitkan langit itu dengan beberapa bintang, dan Kami menjaganya dari setiap syaitan yang berontak.'
      });
    }
  } catch (error) {
    console.error('Error getting daily fact:', error);
    res.status(500).json({ 
      fact_text: 'Fakta sains harian tidak tersedia saat ini.',
      quran_reference: 'QS. An-Nahl: 10 - Dia-lah yang menurunkan air dari langit, sebagian untuk minum dan sebagian (lagi) untuk (menumbuhkan) tumbuh-tumbuhan yang kamu ternakkan.'
    });
  }
});

// Endpoint untuk mendapatkan ayat Al-Qur'an
app.get('/api/quran', async (req, res) => {
  try {
    const { surah, ayah, from, to, search } = req.query;
    
    if (search) {
      // Mode pencarian
      const [rows] = await pool.execute(
        'SELECT id, surah_number, ayah_number, arabic_text, translation, transliteration FROM quran_ayahs WHERE arabic_text LIKE ? OR translation LIKE ? LIMIT 10',
        [`%${search}%`, `%${search}%`]
      );
      return res.json({ search_results: rows });
    } else if (surah && from && to) {
      // Mode rentang ayat
      const [rows] = await pool.execute(
        'SELECT * FROM quran_ayahs WHERE surah_number = ? AND ayah_number BETWEEN ? AND ? ORDER BY ayah_number',
        [surah, from, to]
      );
      return res.json({ surah: parseInt(surah), ayah_range: rows });
    } else if (surah && ayah) {
      // Mode satu ayat
      const [rows] = await pool.execute(
        'SELECT * FROM quran_ayahs WHERE surah_number = ? AND ayah_number = ?',
        [surah, ayah]
      );
      
      if (rows.length > 0) {
        // Ambil juga tafsir untuk ayat ini
        const [tafsirRows] = await pool.execute(
          'SELECT tafsir_source, tafsir_text FROM tafsir WHERE ayah_id = ?',
          [rows[0].id]
        );
        
        res.json({ 
          surah: parseInt(surah),
          ayah: { ...rows[0], tafsir: tafsirRows } 
        });
      } else {
        res.status(404).json({ error: 'Ayat tidak ditemukan' });
      }
    } else {
      res.status(400).json({ error: 'Parameter tidak lengkap' });
    }
  } catch (error) {
    console.error('Error getting Quran ayah:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Endpoint untuk registrasi pengguna
app.post('/api/register', async (req, res) => {
  try {
    const { username, email, password } = req.body;
    
    // Validasi input
    if (!username || !email || !password) {
      return res.status(400).json({ error: 'Semua field harus diisi' });
    }
    
    // Cek apakah username atau email sudah digunakan
    const [existingUsers] = await pool.execute(
      'SELECT id FROM users WHERE username = ? OR email = ?',
      [username, email]
    );
    
    if (existingUsers.length > 0) {
      return res.status(409).json({ error: 'Username atau email sudah digunakan' });
    }
    
    // Hash password (dalam implementasi nyata, gunakan bcrypt)
    const passwordHash = require('crypto').createHash('sha256').update(password).digest('hex');
    
    // Simpan pengguna ke database
    const [result] = await pool.execute(
      'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)',
      [username, email, passwordHash]
    );
    
    res.status(201).json({ 
      message: 'Registrasi berhasil',
      user_id: result.insertId
    });
  } catch (error) {
    console.error('Error registering user:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Endpoint untuk login pengguna
app.post('/api/login', async (req, res) => {
  try {
    const { username, password } = req.body;
    
    if (!username || !password) {
      return res.status(400).json({ error: 'Username dan password harus diisi' });
    }
    
    // Hash password untuk perbandingan
    const passwordHash = require('crypto').createHash('sha256').update(password).digest('hex');
    
    // Cari pengguna
    const [users] = await pool.execute(
      'SELECT id, username, email FROM users WHERE (username = ? OR email = ?) AND password_hash = ?',
      [username, username, passwordHash]
    );
    
    if (users.length === 0) {
      return res.status(401).json({ error: 'Username atau password salah' });
    }
    
    // Dalam implementasi nyata, buat session atau token di sini
    res.json({
      message: 'Login berhasil',
      user: users[0]
    });
  } catch (error) {
    console.error('Error logging in user:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Endpoint untuk mendapatkan profil pengguna
app.get('/api/profile', async (req, res) => {
  // Dalam implementasi nyata, verifikasi otentikasi di sini
  try {
    const userId = req.query.userId; // Dalam implementasi nyata, dapatkan dari token/session
    
    if (!userId) {
      return res.status(401).json({ error: 'User tidak terotentikasi' });
    }
    
    // Ambil informasi pengguna
    const [users] = await pool.execute(
      'SELECT id, username, email, created_at FROM users WHERE id = ?',
      [userId]
    );
    
    if (users.length === 0) {
      return res.status(404).json({ error: 'Pengguna tidak ditemukan' });
    }
    
    // Ambil riwayat pertanyaan pengguna
    const [questions] = await pool.execute(
      'SELECT id, question, answer, quran_reference, created_at FROM user_questions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10',
      [userId]
    );
    
    res.json({
      user: users[0],
      questions: questions
    });
  } catch (error) {
    console.error('Error getting user profile:', error);
    res.status(500).json({ error: 'Internal server error' });
  }
});

// Jalankan server
app.listen(port, () => {
  console.log(`Server berjalan di http://localhost:${port}`);
  console.log('Science-Qur\'an Integration Node.js backend siap digunakan');
});