<?php
require_once 'koneksi.php';
$kriteria_list = $conn->query("SELECT * FROM tb_kriteria");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIGFORGE // Sistem Pakar Rakitan PC Gaming - Metode Weighting</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <!-- File CSS Eksternal -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="ambient-orb-1"></div>
<div class="ambient-orb-2"></div>

<div class="container">
    <!-- TOP COMMAND BAR -->
    <header class="hud-header">
        <div class="brand-zone">
            <div class="telemetry-tag">
                <span class="pulse-dot"></span>
                AI INFERENCE ENGINE // MULTI-CRITERIA WEIGHTING (W × S)
            </div>
            <h1 class="hud-title">RIG<span>FORGE</span> // SISTEM PAKAR RAKITAN PC KUSTOM</h1>
            <p class="hud-subtitle">Optimasi Komponen Perangkat Keras Presisi Berdasarkan Anggaran & Bobot Kebutuhan Pengguna</p>
        </div>
        <div class="hud-meta">
            <div class="engine-pill">
                MODEL INFERENSI
                <strong>WEIGHTING Σ(Wᵢ × Sᵢ)</strong>
            </div>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak Spesifikasi</button>
        </div>
    </header>

    <div class="workspace-grid">
        <!-- PANEL KIRI: KONTROL KONSULTASI -->
        <aside class="tactical-panel">
            <div class="section-heading">
                <span>Parameter Konsultasi</span>
                <span class="code-tag">SYS_INPUT</span>
            </div>

            <form id="formPakar">
                <div class="control-block">
                    <label class="control-label">
                        1. Profil Kebutuhan Utama
                        <span>[MODE]</span>
                    </label>
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

                <div class="control-block">
                    <label class="control-label">
                        2. Batas Anggaran Maksimal
                        <span id="liveBudgetLabel">Rp 15.000.000</span>
                    </label>
                    <input type="number" name="budget" id="budget" value="15000000" step="250000" min="5000000" max="50000000" required>
                    <input type="range" id="budgetSlider" class="budget-slider" min="6000000" max="35000000" step="250000" value="15000000">
                    
                    <div class="preset-matrix">
                        <button type="button" class="chip-btn" onclick="setBudget(7000000)">7 Juta</button>
                        <button type="button" class="chip-btn active" onclick="setBudget(15000000)">15 Juta</button>
                        <button type="button" class="chip-btn" onclick="setBudget(22000000)">22 Juta</button>
                        <button type="button" class="chip-btn" onclick="setBudget(30000000)">30 Juta</button>
                    </div>
                </div>

                <button type="submit" class="btn-ignite">⚡ Eksekusi Mesin Inferensi</button>
            </form>

            <!-- TELEMETRI BOBOT PAKAR -->
            <div class="weight-telemetry">
                <div class="wt-header">
                    <span>Matriks Bobot Pakar (W)</span>
                    <span class="wt-sum">ΣW = 1.00</span>
                </div>

                <div class="wt-item">
                    <div class="wt-top">
                        <span>Performa Gaming (W₁)</span>
                        <span class="wt-val text-crimson" id="disp_w1">0.60</span>
                    </div>
                    <div class="wt-track">
                        <div class="wt-fill bg-crimson" id="bar_w1" style="width:60%;"></div>
                    </div>
                </div>

                <div class="wt-item">
                    <div class="wt-top">
                        <span>Beban Rendering / 3D (W₂)</span>
                        <span class="wt-val text-ice" id="disp_w2">0.20</span>
                    </div>
                    <div class="wt-track">
                        <div class="wt-fill bg-ice" id="bar_w2" style="width:20%;"></div>
                    </div>
                </div>

                <div class="wt-item wt-last">
                    <div class="wt-top">
                        <span>Efisiensi Harga / Value (W₃)</span>
                        <span class="wt-val text-volt" id="disp_w3">0.20</span>
                    </div>
                    <div class="wt-track">
                        <div class="wt-fill bg-volt" id="bar_w3" style="width:20%;"></div>
                    </div>
                </div>

                <div class="formula-banner">
                    Skor Akhir = (W₁×S₁) + (W₂×S₂) + (W₃×S₃)
                </div>
            </div>
        </aside>

        <!-- PANEL KANAN: HASIL RAKITAN & TRANSPARANSI -->
        <main class="tactical-panel">
            <div class="section-heading">
                <span>Konfigurasi Rakitan Optimal</span>
                <span class="code-tag">INFERENCE_OUTPUT</span>
            </div>

            <div id="areaHasil">
                <p class="loading-text">Menginisialisasi mesin inferensi...</p>
            </div>
        </main>
    </div>
</div>

<!-- File JavaScript Eksternal -->
<script src="script.js"></script>
</body>
</html>
