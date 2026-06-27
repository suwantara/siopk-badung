<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background:var(--tanah-gelap);border:none;">
            <div class="modal-header" style="border-bottom:1px solid rgba(200,146,42,0.2);">
                <small id="fotoKet" style="color:var(--abu);"></small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <img id="fotoSrc" src="" style="width:100%;border-radius:3px;max-height:70vh;object-fit:contain;">
            </div>
        </div>
    </div>
</div>

<script>
function openFoto(src, ket) {
    document.getElementById('fotoSrc').src = src;
    document.getElementById('fotoKet').textContent = ket || '';
    new bootstrap.Modal(document.getElementById('modalFoto')).show();
}
</script>
