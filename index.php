<?php
require_once 'koneksi.php';
$kriteria_list = $conn->query("SELECT * FROM tb_kriteria");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar Rakitan PC Gaming - Metode Weighting</title>
    <style>
        :root {
            --bg-dark: #090d16;
            --card-glass: rgba(17, 25, 40, 0.78);
            --neon-cyan: #00f2fe;
            --neon-green: #00ff87;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --border-glass: rgba(0, 242, 254, 0.22);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(0, 242, 254, 0.08) 0%, transparent 35%),
                radial-gradient(circle at 85% 85%, rgba(0, 255, 135, 0.07) 0%, transparent 35%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 30px 20px;
        }
        .container { max-width: 1180px; margin: 0 auto; }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            background: var(--card-glass);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            box-shadow: 0 0 25px rgba(0, 242, 254, 0.08);
        }
        .badge-ai {
            display: inline-block;
            padding: 5px 14px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--bg-dark);
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-green));
            border-radius: 50px;
            margin-bottom: 12px;
        }
        .header h1 { font-size: 1.85rem; margin-bottom: 8px; color: #fff; }
        .header p { color: var(--text-muted); font-size: 0.95rem; }

        /* Grid Layout */
        .main-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 24px;
        }
        @media (max-width: 900px) { .main-grid { grid-template-columns: 1fr; } }

        /* Panel Glass */
        .glass-panel {
            background: var(--card-glass);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(12px);
        }
        .panel-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: var(--neon-cyan);
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding-bottom: 10px;
        }

        /* Form Controls */
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 0.88rem; color: var(--text-muted); margin-bottom: 8px; }
        select, input[type="number"] {
            width: 100%;
            padding: 12px 14px;
            background: rgba(10, 15, 28, 0.9);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: 0.2s;
        }
        select:focus, input[type="number"]:focus {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 12px rgba(0, 242, 254, 0.25);
        }
        .preset-budgets {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
        }
        .btn-preset {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            color: var(--text-main);
            padding: 7px;
            border-radius: 8px;
            font-size: 0.78rem;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-preset:hover {
            border-color: var(--neon-cyan);
            color: var(--neon-cyan);
            background: rgba(0, 242, 254, 0.08);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-green));
            border: none;
            border-radius: 10px;
            color: #06101e;
            font-weight: 800;
            font-size: 0.95rem;
            cursor: pointer;
            transition: 0.25s;
            margin-top: 8px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 242, 254, 0.35);
        }

        /* Info Bobot Box */
        .weight-box {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0, 242, 254, 0.05);
            border: 1px dashed rgba(0, 242, 254, 0.3);
            border-radius: 12px;
            font-size: 0.85rem;
        }
        .weight-row { display: flex; justify-content: space-between; margin: 6px 0; }
        .weight-val { font-weight: 700; color: var(--neon-green); }

        /* Stats Cards */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }
        .stat-label { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 5px; }
        .stat-value { font-size: 1.25rem; font-weight: 800; color: var(--neon-cyan); }

        /* Component Cards */
        .rakitan-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        @media (max-width: 680px) { .rakitan-grid { grid-template-columns: 1fr; } }
        .comp-card {
            background: rgba(12, 18, 32, 0.9);
            border: 1px solid rgba(255,255,255,0.08);
            border-left: 4px solid var(--neon-cyan);
            border-radius: 12px;
            padding: 15px;
            transition: 0.2s;
        }
        .comp-card:hover { border-color: var(--neon-green); transform: translateY(-2px); }
        .comp-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .comp-cat {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(0, 242, 254, 0.12);
            color: var(--neon-cyan);
        }
        .comp-score {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--neon-green);
        }
        .comp-name { font-size: 0.98rem; font-weight: 700; color: #fff; margin-bottom: 6px; }
        .comp-price { font-size: 0.9rem; color: #cbd5e1; margin-bottom: 8px; }
        .comp-formula {
            font-size: 0.75rem;
            color: var(--text-muted);
            background: rgba(255,255,255,0.03);
            padding: 6px 8px;
            border-radius: 6px;
            font-family: Consolas, monospace;
        }

        /* Tabel Perhitungan Detail */
        .table-wrap { overflow-x: auto; margin-top: 22px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); }
        th { background: rgba(0, 242, 254, 0.1); color: var(--neon-cyan); }
        tr.terpilih-row { background: rgba(0, 255, 135, 0.08); }
        .badge-pilih {
            background: var(--neon-green);
            color: #06101e;
            font-weight: 700;
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <span class="badge-ai">EXPERT SYSTEM • METODE WEIGHTING (W × S)</span>
        <h1>Aplikasi Sistem Pakar Rekomendasi Rakitan PC Gaming Kustom</h1>
        <p>Evaluasi Multi-Kriteria Berdasarkan Anggaran dan Kebutuhan Pengguna</p>
    </div>

    <div class="main-grid">
        <!-- PANEL KIRI: INPUT PENGGUNA -->
        <div class="glass-panel">
            <div class="panel-title">⚙️ Parameter Konsultasi Pakar</div>
            <form id="formPakar">
                <div class="form-group">
                    <label>1. Pilih Profil Kebutuhan Utama:</label>
                    <select name="id_kriteria" id="id_kriteria" required>
                        <?php while($k = $kriteria_list->fetch_assoc()): ?>
                            <option value="<?= $k['id_kriteria'] ?>"
                                data-w1="<?= $k['bobot_gaming'] ?>"
                                data-w2="<?= $k['bobot_rendering'] ?>"
                                data-w3="<?= $k['bobot_value'] ?>">
                                <?= htmlspecialchars($k['profil_pengguna']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>2. Masukkan Anggaran Maksimal (Rp):</label>
                    <input type="number" name="budget" id="budget" value="15000000" step="500000" min="5000000" required>
                    <div class="preset-budgets">
                        <button type="button" class="btn-preset" onclick="setBudget(7000000)">Rp 7 Juta</button>
                        <button type="button" class="btn-preset" onclick="setBudget(15000000)">Rp 15 Juta</button>
                        <button type="button" class="btn-preset" onclick="setBudget(28000000)">Rp 28 Juta</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">⚡ Jalankan Mesin Inferensi</button>
            </form>

            <!-- Tampilan Bobot Pakar Aktif -->
            <div class="weight-box">
                <div style="font-weight:700; color:#fff; margin-bottom:8px;">📊 Basis Pengetahuan Bobot (W):</div>
                <div class="weight-row"><span>Bobot Gaming (W₁):</span> <span class="weight-val" id="disp_w1">0.60</span></div>
                <div class="weight-row"><span>Bobot Rendering (W₂):</span> <span class="weight-val" id="disp_w2">0.20</span></div>
                <div class="weight-row"><span>Bobot Value/Harga (W₃):</span> <span class="weight-val" id="disp_w3">0.20</span></div>
                <div style="margin-top:8px; font-size:0.76rem; color:var(--text-muted);">
                    Rumus: <b>Total = (W₁×S₁) + (W₂×S₂) + (W₃×S₃)</b>
                </div>
            </div>
        </div>

        <!-- PANEL KANAN: HASIL INFERENSI -->
        <div class="glass-panel">
            <div class="panel-title">🖥️ Hasil Rekomendasi Rakitan PC Optimal</div>
            <div id="areaHasil">
                <p style="color:var(--text-muted);">Memuat perhitungan mesin inferensi...</p>
            </div>
        </div>
    </div>
</div>

<script>
function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

function setBudget(nominal) {
    document.getElementById('budget').value = nominal;
    jalankanInferensi();
}

function updateTampilanBobot() {
    const sel = document.getElementById('id_kriteria');
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('disp_w1').innerText = opt.getAttribute('data-w1');
    document.getElementById('disp_w2').innerText = opt.getAttribute('data-w2');
    document.getElementById('disp_w3').innerText = opt.getAttribute('data-w3');
}

document.getElementById('id_kriteria').addEventListener('change', function() {
    updateTampilanBobot();
    jalankanInferensi();
});

document.getElementById('formPakar').addEventListener('submit', function(e) {
    e.preventDefault();
    jalankanInferensi();
});

function jalankanInferensi() {
    const formData = new FormData(document.getElementById('formPakar'));
    const areaHasil = document.getElementById('areaHasil');

    fetch('proses_pakar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'budget_kurang') {
            areaHasil.innerHTML = `
                <div style="padding:18px; background:rgba(239,68,68,0.15); border:1px solid #ef4444; border-radius:12px; color:#fca5a5;">
                    <b>⚠️ Anggaran Tidak Mencukupi</b><br>${data.pesan}
                </div>`;
            return;
        }

        let html = `
            <div class="stats-bar">
                <div class="stat-card">
                    <div class="stat-label">Total Biaya Rakitan</div>
                    <div class="stat-value">${formatRupiah(data.total_harga)}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Sisa Anggaran</div>
                    <div class="stat-value" style="color:var(--neon-green);">${formatRupiah(data.sisa_budget)}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Akumulasi Skor Pakar</div>
                    <div class="stat-value">${data.total_skor_akumulasi} <span style="font-size:0.8rem; color:var(--text-muted);">(Avg: ${data.rata_rata_skor})</span></div>
                </div>
            </div>

            <div class="rakitan-grid">
        `;

        const idTerpilih = new Set();
        data.rakitan.forEach(item => {
            idTerpilih.add(item.id_komponen);
            html += `
                <div class="comp-card">
                    <div class="comp-top">
                        <span class="comp-cat">${item.kategori}</span>
                        <span class="comp-score">Skor: ${item.total_skor}</span>
                    </div>
                    <div class="comp-name">${item.nama_komponen}</div>
                    <div class="comp-price">${formatRupiah(item.harga)}</div>
                    <div class="comp-formula">∑ = ${item.rumus_teks}</div>
                </div>
            `;
        });

        html += `</div>`;

        // Tabel Transparansi Perhitungan Semua Komponen (Bukti Akurasi Logika 40%)
        html += `
            <div class="table-wrap">
                <h4 style="margin: 22px 0 10px; color:var(--neon-cyan);">📑 Tabel Transparansi Perhitungan Metode Weighting (Semua Kandidat)</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Nama Komponen</th>
                            <th>Harga</th>
                            <th>S₁ / S₂ / S₃</th>
                            <th>Rumus (W × S)</th>
                            <th>Skor Akhir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        Object.keys(data.semua_katalog).forEach(kat => {
            data.semua_katalog[kat].forEach(item => {
                const isSelected = idTerpilih.has(item.id_komponen);
                html += `
                    <tr class="${isSelected ? 'terpilih-row' : ''}">
                        <td><b>${item.kategori}</b></td>
                        <td>${item.nama_komponen}</td>
                        <td>${formatRupiah(item.harga)}</td>
                        <td>${item.skor_gaming} / ${item.skor_rendering} / ${item.skor_value}</td>
                        <td style="font-family:monospace; font-size:0.78rem;">${item.rumus_teks}</td>
                        <td><b style="color:var(--neon-green);">${item.total_skor}</b></td>
                        <td>${isSelected ? '<span class="badge-pilih">TERPILIH</span>' : '-'}</td>
                    </tr>
                `;
            });
        });

        html += `</tbody></table></div>`;
        areaHasil.innerHTML = html;
    });
}

// Jalankan otomatis saat halaman pertama kali dibuka
window.onload = function() {
    updateTampilanBobot();
    jalankanInferensi();
};
</script>

</body>
</html>