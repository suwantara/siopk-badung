'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-update-opk');
    if (!form) return;

    const apiDesaDinas = form.dataset.apiDesaDinas;
    const MAX_FOTO = parseInt(form.dataset.maxFoto) || 10;
    let existingCount = parseInt(form.dataset.fotoCount) || 0;
    let existingVideoCount = parseInt(form.dataset.videoCount) || 0;
    let existingDokumenCount = parseInt(form.dataset.dokumenCount) || 0;
    let deletedCount = 0;
    let newFileCount = 0;
    const hapusFotoIds = new Set();
    let hapusVideoIds = [];
    let hapusDokumenIds = [];

    // ===== Kecamatan -> Desa Dinas cascading =====
    const selKecamatan = document.getElementById('selKecamatan');
    const selDesaDinas = document.getElementById('selDesaDinas');
    if (selKecamatan) {
        selKecamatan.addEventListener('change', function () {
            const id = this.value;
            selDesaDinas.innerHTML = '<option value="">Memuat...</option>';
            if (!id) {
                selDesaDinas.innerHTML = '<option value="">— Pilih Kecamatan Dulu —</option>';
                return;
            }
            fetch(apiDesaDinas + '?kecamatan_id=' + id)
                .then(r => r.json())
                .then(data => {
                    let html = '<option value="">— Pilih Desa Dinas —</option>';
                    data.forEach(function (d) {
                        html += '<option value="' + d.id + '">' + d.nama + '</option>';
                    });
                    selDesaDinas.innerHTML = html;
                })
                .catch(function () {
                    selDesaDinas.innerHTML = '<option value="">Gagal memuat</option>';
                });
        });
    }

    // ===== Tipe Pelapor radio =====
    const checkedTipe = document.querySelector('[name="tipe_pelapor"]:checked');
    if (checkedTipe) {
        const lbl = document.getElementById('lbl-' + checkedTipe.value);
        if (lbl) lbl.classList.add('selected');
    }

    window.setTipe = function (el, val) {
        document.querySelectorAll('.edit-tipe-label').forEach(function (l) {
            l.classList.remove('selected');
        });
        el.classList.add('selected');
    };

    // ===== FOTO MANAGEMENT =====
    function updateQuota() {
        const current = existingCount - deletedCount + newFileCount;
        const fotoCountEl = document.getElementById('foto-count');
        if (fotoCountEl) {
            fotoCountEl.textContent = current;
            fotoCountEl.style.color = current >= MAX_FOTO ? 'var(--merah)' : '';
        }
        const warn = document.getElementById('foto-quota-warn');
        const btn = document.getElementById('btn-tambah-foto');
        const input = document.getElementById('input-fotos');
        if (current >= MAX_FOTO) {
            if (warn) warn.style.display = 'inline';
            if (btn) { btn.style.opacity = '0.4'; btn.style.pointerEvents = 'none'; }
            if (input) input.disabled = true;
        } else {
            if (warn) warn.style.display = 'none';
            if (btn) { btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }
            if (input) input.disabled = false;
        }
    }

    window.handleFotoChange = function (input) {
        const files = Array.from(input.files);
        const current = existingCount - deletedCount + newFileCount;
        const available = MAX_FOTO - current;
        if (files.length > available) {
            if (typeof swalToast === 'function') {
                swalToast('warning', 'Maksimal ' + MAX_FOTO + ' foto. Hanya ' + available + ' slot tersisa.');
            }
            input.value = '';
            return;
        }
        const preview = document.getElementById('foto-preview');
        if (preview) preview.innerHTML = '';
        files.forEach(function (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'edit-foto-preview';
                div.innerHTML = '<img src="' + e.target.result + '" alt=""><span class="edit-foto-preview-badge">BARU</span>';
                if (preview) preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        newFileCount = files.length;
        updateQuota();
    };

    document.querySelectorAll('.btn-hapus-foto').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            if (!hapusFotoIds.has(id)) {
                hapusFotoIds.add(id);
                deletedCount++;
            }
            document.getElementById('hapus_foto_ids').value = Array.from(hapusFotoIds).join(',');
            const wrapper = this.closest('.edit-foto-thumb');
            if (wrapper) { wrapper.style.opacity = '0.3'; }
            this.disabled = true;
            this.style.display = 'none';
            updateQuota();
        });
    });

    document.querySelectorAll('.btn-jadikan-utama').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('foto_utama_id').value = this.dataset.id;
            document.querySelectorAll('.btn-jadikan-utama').forEach(function (b) {
                b.textContent = '\u2605';
                b.style.background = 'rgba(0,0,0,0.5)';
            });
            this.textContent = '\u25CF';
            this.style.background = 'var(--emas)';
        });
    });

    // ===== VIDEO MANAGEMENT =====
    document.querySelectorAll('.btn-hapus-video').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = parseInt(this.dataset.id);
            if (!hapusVideoIds.includes(id)) {
                hapusVideoIds.push(id);
            }
            document.getElementById('hapus_video_ids').value = hapusVideoIds.join(',');
            const item = this.closest('.edit-media-item');
            if (item) { item.style.opacity = '0.3'; }
            this.disabled = true;
            updateVideoCount();
        });
    });

    const btnTambahVideo = document.getElementById('btn-tambah-video');
    if (btnTambahVideo) {
        btnTambahVideo.addEventListener('click', function () {
            const container = document.getElementById('video-inputs');
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm mb-2 video-link-row';
            row.innerHTML = '<span class="input-group-text"><i class="bi bi-link-45deg"></i></span>' +
                '<input type="url" name="video_links[]" class="form-control" placeholder="https://youtube.com/watch?v=...">' +
                '<button type="button" class="btn btn-sm btn-hapus-video-link" style="background:var(--merah);color:white;border:none;" title="Hapus">&times;</button>';
            container.appendChild(row);
            row.querySelector('.btn-hapus-video-link').addEventListener('click', function () {
                row.remove();
            });
        });
    }

    document.querySelectorAll('.btn-hapus-video-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = this.closest('.video-link-row');
            if (row) row.remove();
        });
    });

    function updateVideoCount() {
        const total = document.querySelectorAll('.video-item-existing').length - hapusVideoIds.length;
        const el = document.getElementById('video-count');
        if (el) el.textContent = Math.max(0, total);
    }

    // ===== DOKUMEN MANAGEMENT =====
    document.querySelectorAll('.btn-hapus-dokumen').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = parseInt(this.dataset.id);
            if (!hapusDokumenIds.includes(id)) {
                hapusDokumenIds.push(id);
            }
            document.getElementById('hapus_dokumen_ids').value = hapusDokumenIds.join(',');
            const item = this.closest('.edit-media-item');
            if (item) { item.style.opacity = '0.3'; }
            this.disabled = true;
            updateDokumenCount();
        });
    });

    window.handleDokumenChange = function (input) {
        const preview = document.getElementById('dokumen-preview');
        if (preview) preview.innerHTML = '';
        Array.from(input.files).forEach(function (file) {
            const div = document.createElement('div');
            div.style.cssText = 'padding:4px 10px;background:var(--surface-hijau);border-radius:4px;margin-bottom:4px;display:flex;align-items:center;gap:6px;';
            div.innerHTML = '<i class="bi bi-file-earmark-check" style="color:var(--hijau);"></i>' +
                '<span class="t-caption">' + file.name + '</span>' +
                '<small style="color:var(--abu);">(' + formatSize(file.size) + ')</small>';
            if (preview) preview.appendChild(div);
        });
    };

    function updateDokumenCount() {
        const total = document.querySelectorAll('.dokumen-item-existing').length - hapusDokumenIds.length;
        const el = document.getElementById('dokumen-count');
        if (el) el.textContent = Math.max(0, total);
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    // ===== Init =====
    updateQuota();
    updateVideoCount();
    updateDokumenCount();
});
