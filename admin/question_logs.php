<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Logs - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #001f3f, #0a3d62);
            color: white;
            padding: 20px 0;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar h3 {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin: 5px 0;
        }
        
        .sidebar-menu a {
            display: block;
            color: #ddd;
            text-decoration: none;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: #f1c40f;
            border-left: 4px solid #f1c40f;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f5f7fa;
        }
        
        .header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #001f3f;
            margin: 0;
            font-size: 1.5rem;
        }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            color: #001f3f;
            margin: 0;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #001f3f;
        }
        
        .stat-card .number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #001f3f;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #001f3f;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #001f3f, #0a3d62);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #0a3d62, #145a82);
        }
        
        .view-response {
            display: inline-block;
            margin-top: 5px;
            color: #001f3f;
            text-decoration: underline;
            cursor: pointer;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .response-content {
            margin-top: 15px;
            line-height: 1.6;
        }
        
        .response-content h4 {
            color: #001f3f;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .response-content p {
            margin: 10px 0;
        }
        
        .video-link {
            color: #3498db;
            text-decoration: none;
        }
        
        .video-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h3><i class="fas fa-book-quran"></i> SQI Admin</h3>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="api_settings.php"><i class="fas fa-key"></i> API Settings</a></li>
                <li><a href="video_manager.php"><i class="fas fa-video"></i> Video Manager</a></li>
                <li><a href="system_instruction.php"><i class="fas fa-robot"></i> System Instruction</a></li>
                <li><a href="#" class="active"><i class="fas fa-comments"></i> Question Logs</a></li>
                <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Riwayat Pertanyaan</h1>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-comments"></i> Log Pertanyaan Pengguna</h2>
                </div>
                
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="number">142</div>
                        <div class="label">Total Pertanyaan</div>
                    </div>
                    <div class="stat-card">
                        <div class="number">86</div>
                        <div class="label">Video Ditampilkan</div>
                    </div>
                    <div class="stat-card">
                        <div class="number">24</div>
                        <div class="label">Hari Ini</div>
                    </div>
                    <div class="stat-card">
                        <div class="number">95%</div>
                        <div class="label">Akurasi Cocok</div>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pertanyaan Pengguna</th>
                            <th>Video Terkait</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Apa yang dikatakan Al-Qur'an tentang penciptaan manusia?</td>
                            <td><a href="https://www.youtube.com/embed/abcd1234" target="_blank" class="video-link">Lihat Video</a></td>
                            <td>2025-01-15 10:30</td>
                            <td><span class="view-response" onclick="showResponse(1)">Lihat Respons</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Bagaimana Al-Qur'an menjelaskan tentang sistem tata surya?</td>
                            <td><a href="https://www.youtube.com/embed/xyz5678" target="_blank" class="video-link">Lihat Video</a></td>
                            <td>2025-01-15 09:45</td>
                            <td><span class="view-response" onclick="showResponse(2)">Lihat Respons</span></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Apa yang dikatakan Al-Qur'an tentang air dan kehidupan?</td>
                            <td><a href="https://www.youtube.com/embed/def456" target="_blank" class="video-link">Lihat Video</a></td>
                            <td>2025-01-15 08:22</td>
                            <td><span class="view-response" onclick="showResponse(3)">Lihat Respons</span></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Bagaimana Al-Qur'an menjelaskan tentang fase embrio manusia?</td>
                            <td><a href="https://www.youtube.com/embed/efg9012" target="_blank" class="video-link">Lihat Video</a></td>
                            <td>2025-01-14 16:40</td>
                            <td><span class="view-response" onclick="showResponse(4)">Lihat Respons</span></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Apa yang dikatakan Al-Qur'an tentang gunung dan fungsinya?</td>
                            <td><a href="https://www.youtube.com/embed/ghi345" target="_blank" class="video-link">Lihat Video</a></td>
                            <td>2025-01-14 14:15</td>
                            <td><span class="view-response" onclick="showResponse(5)">Lihat Respons</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal for viewing response details -->
    <div id="responseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Detail Respons AI</h3>
            <div id="responseContent" class="response-content">
                <!-- Content will be filled by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        function showResponse(id) {
            // In a real implementation, fetch response details by ID
            let content = '';
            
            switch(id) {
                case 1:
                    content = `
                        <h4>Pertanyaan:</h4>
                        <p>Apa yang dikatakan Al-Qur'an tentang penciptaan manusia?</p>
                        
                        <h4>Respons AI:</h4>
                        <p><strong>Penjelasan Ilmiah:</strong> Manusia berasal dari proses evolusi yang panjang. Tubuh manusia terdiri dari elemen-elemen dasar seperti karbon, hidrogen, oksigen, dan nitrogen yang berasal dari bintang-bintang tua. Dalam kandungan, manusia mengalami berbagai tahap perkembangan dari zigot hingga menjadi janin yang sempurna.</p>
                        
                        <p><strong>Dalil Al-Qur'an:</strong> QS. Al-Mu'minun: 12-14 - وَلَقَدْ خَلَقْنَا الْإِنْسَانَ مِنْ سُلَالَةٍ مِنْ طِينٍ. ثُمَّ جَعَلْنَاهُ نُطْفَةً فِي قَرَارٍ مَكِينٍ. ثُمَّ خَلَقْنَا النُّطْفَةَ عَلَقَةً فَخَلَقْنَا الْعَلَقَةَ مُضْغَةً فَخَلَقْنَا الْمُضْغَةَ عِظَامًا فَكَسَوْنَا الْعِظَامَ لَحْمًا ثُمَّ أَنْشَأْنَاهُ خَلْقًا آخَرَ فَتَبَارَكَ اللَّهُ أَحْسَنُ الْخَالِقِينَ</p>
                        <p>Artinya: Dan sungguh, Kami telah menciptakan manusia dari saripati (berasal) dari tanah. Kemudian Kami jadikan saripati itu air mani (yang disimpan) dalam tempat yang kokoh. Lalu air mani itu Kami jadikan segumpal darah, lalu segumpal darah itu Kami jadikan segumpal daging, dan segumpal daging itu Kami jadikan tulang belulang, lalu tulang belulang itu Kami bungkus dengan daging. Kemudian Kami jadikan dia makhluk yang (berbentuk) lain. Mahasuci Allah, Pencipta Yang Paling Baik.</p>
                        
                        <h4>Analisis Integrasi:</h4>
                        <p>Deskripsi Al-Qur'an tentang perkembangan janin mencakup tahapan-tahapan yang sesuai dengan penemuan ilmiah modern, yaitu dari sperma menjadi zigot, kemudian menjadi gumpalan darah (alaqah), segumpal daging (mudghah), tahap pembentukan tulang (idham), dan akhirnya dilapisi dengan daging (lahm).</p>
                        
                        <h4>Video Terkait:</h4>
                        <p>Proses Penciptaan Manusia Menurut Sains dan Al-Qur'an (https://www.youtube.com/embed/abcd1234)</p>
                    `;
                    break;
                case 2:
                    content = `
                        <h4>Pertanyaan:</h4>
                        <p>Bagaimana Al-Qur'an menjelaskan tentang sistem tata surya?</p>
                        
                        <h4>Respons AI:</h4>
                        <p><strong>Penjelasan Ilmiah:</strong> Sistem tata surya terdiri dari Matahari sebagai pusat dan delapan planet yang mengelilinginya, beserta asteroid, komet, dan benda langit lainnya. Setiap planet berjalan dalam orbit yang stabil berkat gaya gravitasi. Sains modern menemukan bahwa sistem tata surya terbentuk sekitar 4,6 miliar tahun yang lalu dari awan gas dan debu.</p>
                        
                        <p><strong>Dalil Al-Qur'an:</strong> QS. Anbiya: 33 - وَسَخَّرَ لَكُمُ الشَّمْسَ وَالْقَمَرَ دَائِبَيْنِ وَسَخَّرَ لَكُمُ اللَّيْلَ وَالنَّهَارَ</p>
                        <p>Artinya: Dan Dia menundukkan (membuat bermanfaat) bagi kamu matahari dan bulan yang terus-menerus (beredar), dan Dia menundukkan (membuat bermanfaat) bagimu malam dan siang.</p>
                        
                        <h4>Analisis Integrasi:</h4>
                        <p>Kata "sakhar" (سَخَّرَ) dalam ayat di atas berarti menundukkan atau membuat bermanfaat. Ini menunjukkan bahwa fenomena astrofisika seperti gerak planet, siang-malam, dan sebagainya tidak hanya terjadi secara alami tetapi juga disengaja untuk kepentingan manusia, menunjukkan perancangan yang sangat kompleks.</p>
                        
                        <h4>Video Terkait:</h4>
                        <p>Big Bang dan Penciptaan Alam Semesta dalam Al-Qur'an (https://www.youtube.com/embed/xyz5678)</p>
                    `;
                    break;
                default:
                    content = `
                        <h4>Detail Respons AI</h4>
                        <p>Pertanyaan: Contoh pertanyaan tentang sains dan Al-Qur'an</p>
                        <p>Respons AI: Jawaban ilmiah dan referensi Al-Qur'an terkait pertanyaan tersebut.</p>
                        <p>Video Terkait: URL video pendukung</p>
                    `;
            }
            
            document.getElementById('responseContent').innerHTML = content;
            document.getElementById('responseModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('responseModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('responseModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>