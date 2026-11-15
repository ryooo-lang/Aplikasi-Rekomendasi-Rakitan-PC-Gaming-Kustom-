let globalKatalog = {};
let globalIdTerpilih = new Set();

function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

function setBudget(nominal) {
    document.getElementById('budget').value = nominal;
    document.getElementById('budgetSlider').value = nominal;
    document.getElementById('liveBudgetLabel').innerText = formatRupiah(nominal);
    
    document.querySelectorAll('.chip-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('onclick').includes(String(nominal))) {
            btn.classList.add('active');
        }
    });
    jalankanInferensi();
}

// Event Listeners untuk Slider & Input Anggaran
document.getElementById('budgetSlider').addEventListener('input', function() {
    document.getElementById('budget').value = this.value;
    document.getElementById('liveBudgetLabel').innerText = formatRupiah(this.value);
});

document.getElementById('budgetSlider').addEventListener('change', function() {
    jalankanInferensi();
});

document.getElementById('budget').addEventListener('input', function() {
    document.getElementById('budgetSlider').value = this.value;
    document.getElementById('liveBudgetLabel').innerText = formatRupiah(this.value || 0);
});

// Update Bar Bobot Pakar (W1, W2, W3)
function updateTampilanBobot() {
    const sel = document.getElementById('id_kriteria');
    if (!sel || sel.options.length === 0) return;
    const opt = sel.options[sel.selectedIndex];
    const w1 = parseFloat(opt.getAttribute('data-w1')) || 0;
    const w2 = parseFloat(opt.getAttribute('data-w2')) || 0;
    const w3 = parseFloat(opt.getAttribute('data-w3')) || 0;

    document.getElementById('disp_w1').innerText = w1.toFixed(2);
    document.getElementById('disp_w2').innerText = w2.toFixed(2);
    document.getElementById('disp_w3').innerText = w3.toFixed(2);

    document.getElementById('bar_w1').style.width = (w1 * 100) + '%';
    document.getElementById('bar_w2').style.width = (w2 * 100) + '%';
    document.getElementById('bar_w3').style.width = (w3 * 100) + '%';
}

document.getElementById('id_kriteria').addEventListener('change', function() {
    updateTampilanBobot();
    jalankanInferensi();
});

document.getElementById('formPakar').addEventListener('submit', function(e) {
    e.preventDefault();
    jalankanInferensi();
});

// Efek Spotlight mengikuti Kursor Mouse pada Kartu Hardware
function aktifkanSpotlightKartu() {
    document.querySelectorAll('.hw-card').forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
            card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
        });
    });
}

// Render & Filter Tabel Kandidat Komponen
function renderTabelKatalog(kategoriFilter = 'ALL') {
    const tbody = document.getElementById('tbodyKatalog');
    if (!tbody) return;

    document.querySelectorAll('.tab-filter').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.kat === kategoriFilter);
    });

    let rowsHtml = '';
    Object.keys(globalKatalog).forEach(kat => {
        if (kategoriFilter !== 'ALL' && kat !== kategoriFilter) return;
        globalKatalog[kat].forEach(item => {
            const isSelected = globalIdTerpilih.has(String(item.id_komponen));
            rowsHtml += `
                <tr class="${isSelected ? 'selected-row' : ''}">
                    <td style="font-family:'JetBrains Mono', monospace; font-weight:700; color:${isSelected ? 'var(--crimson)' : 'var(--text-secondary)'};">${item.kategori}</td>
                    <td style="font-weight:600;">${item.nama_komponen}</td>
                    <td style="font-family:'JetBrains Mono', monospace; color:var(--ice);">${formatRupiah(item.harga)}</td>
                    <td style="font-family:'JetBrains Mono', monospace;">${item.skor_gaming} / ${item.skor_rendering} / ${item.skor_value}</td>
                    <td style="font-family:'JetBrains Mono', monospace; font-size:0.75rem; color:var(--text-secondary);">${item.rumus_teks}</td>
                    <td style="font-family:'JetBrains Mono', monospace; font-weight:700; color:var(--volt);">${item.total_skor}</td>
                    <td>${isSelected ? '<span class="status-pill">TERPILIH</span>' : '<span style="color:var(--text-dim); font-family:monospace;">KANDIDAT</span>'}</td>
                </tr>
            `;
        });
    });
    tbody.innerHTML = rowsHtml;
}

// Fungsi Utama Pemanggilan AJAX ke Mesin Inferensi (proses_pakar.php)
function jalankanInferensi() {
    const formData = new FormData(document.getElementById('formPakar'));
    const areaHasil = document.getElementById('areaHasil');

    fetch('proses_pakar.php', {
        method: 'POST',
        body: formData
    })
    .then(async res => {
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (err) {
            throw new Error("Respons server bukan JSON valid: " + text);
        }
    })
    .then(data => {
        if (data.status === 'error' || data.status === 'budget_kurang') {
            areaHasil.innerHTML = `
                <div class="alert-error">
                    <strong style="color:var(--crimson); display:block; margin-bottom:6px; font-size:1rem;">[!] PERINGATAN ANGGARAN MINIMUM</strong>
                    ${data.pesan}
                </div>`;
            return;
        }

        globalKatalog = data.semua_katalog;
        globalIdTerpilih = new Set();
        const persenTerpakai = Math.min(100, Math.round((data.total_harga / data.budget_input) * 100));

        let html = `
            <div class="telemetry-kpis">
                <div class="kpi-box">
                    <div class="kpi-label">Total Biaya Rakitan</div>
                    <div class="kpi-number text-ice">${formatRupiah(data.total_harga)}</div>
                    <div class="kpi-sub">Dari batas ${formatRupiah(data.budget_input)}</div>
                </div>
                <div class="kpi-box">
                    <div class="kpi-label">Sisa Anggaran</div>
                    <div class="kpi-number text-volt">${formatRupiah(data.sisa_budget)}</div>
                    <div class="kpi-sub">Efisiensi Alokasi: ${persenTerpakai}%</div>
                </div>
                <div class="kpi-box">
                    <div class="kpi-label">Skor Pakar Akumulasi</div>
                    <div class="kpi-number text-crimson">${data.total_skor_akumulasi}</div>
                    <div class="kpi-sub">Rata-rata Skor: ${data.rata_rata_skor} / 100</div>
                </div>
            </div>

            <div class="budget-utilization">
                <div class="bu-head">
                    <span>UTILISASI ANGGARAN RAKITAN</span>
                    <span class="text-volt">${persenTerpakai}% TERPAKAI</span>
                </div>
                <div class="bu-bar">
                    <div class="bu-progress" style="width:${persenTerpakai}%;"></div>
                </div>
            </div>

            <div class="rig-grid">
        `;

        data.rakitan.forEach(item => {
            globalIdTerpilih.add(String(item.id_komponen));
            html += `
                <article class="hw-card">
                    <div class="hw-header">
                        <span class="hw-type">◆ ${item.kategori}</span>
                        <span class="hw-score-badge">SKOR: ${item.total_skor}</span>
                    </div>
                    <h3 class="hw-title">${item.nama_komponen}</h3>
                    <div class="hw-price">${formatRupiah(item.harga)}</div>

                    <div class="subscore-bars">
                        <div class="sb-col">
                            <div class="sb-label"><span>GAMING</span> <b>${item.skor_gaming}</b></div>
                            <div class="sb-mini-track"><div class="sb-mini-fill bg-crimson" style="width:${item.skor_gaming}%;"></div></div>
                        </div>
                        <div class="sb-col">
                            <div class="sb-label"><span>RENDER</span> <b>${item.skor_rendering}</b></div>
                            <div class="sb-mini-track"><div class="sb-mini-fill bg-ice" style="width:${item.skor_rendering}%;"></div></div>
                        </div>
                        <div class="sb-col">
                            <div class="sb-label"><span>VALUE</span> <b>${item.skor_value}</b></div>
                            <div class="sb-mini-track"><div class="sb-mini-fill bg-volt" style="width:${item.skor_value}%;"></div></div>
                        </div>
                    </div>

                    <div class="hw-math">∑(W×S) = ${item.rumus_teks}</div>
                </article>
            `;
        });

        html += `</div>`;

        const daftarKat = Object.keys(globalKatalog);
        html += `
            <div class="audit-section">
                <div class="audit-header">
                    <div class="audit-title">Matriks Transparansi Perhitungan (Semua Kandidat)</div>
                    <div class="filter-tabs">
                        <button type="button" class="tab-filter active" data-kat="ALL" onclick="renderTabelKatalog('ALL')">SEMUA (${Object.values(globalKatalog).flat().length})</button>
                        ${daftarKat.map(k => `<button type="button" class="tab-filter" data-kat="${k}" onclick="renderTabelKatalog('${k}')">${k}</button>`).join('')}
                    </div>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Komponen</th>
                                <th>Harga</th>
                                <th>S₁ / S₂ / S₃</th>
                                <th>Kalkulasi (W × S)</th>
                                <th>Skor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKatalog"></tbody>
                    </table>
                </div>
            </div>
        `;

        areaHasil.innerHTML = html;
        renderTabelKatalog('ALL');
        aktifkanSpotlightKartu();
    })
    .catch(err => {
        areaHasil.innerHTML = `
            <div class="alert-error">
                <b>❌ Gagal Memuat Mesin Inferensi:</b><br>${err.message}
            </div>`;
    });
}

// Jalankan otomatis saat halaman pertama kali dimuat
window.onload = function() {
    updateTampilanBobot();
    jalankanInferensi();
};
