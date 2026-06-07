{{-- Buscador rápido global (Ctrl/Cmd + K) — solo admin · JS vanilla --}}
<div id="rh-buscador" style="display:none; position:fixed; inset:0; z-index:9999; align-items:flex-start; justify-content:center; padding:10vh 16px 16px; background:rgba(15,23,42,0.55); backdrop-filter:blur(2px);">
    <div id="rh-buscador-box" style="width:100%; max-width:600px; background:#fff; border-radius:14px; box-shadow:0 20px 50px rgba(0,0,0,0.3); overflow:hidden;">
        {{-- Caja de búsqueda --}}
        <div style="display:flex; align-items:center; gap:10px; padding:14px 18px; border-bottom:1px solid #e2e8f0;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px;color:#94a3b8;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input id="rh-buscador-input" type="text" autocomplete="off" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="es-MX"
                placeholder="Buscar empresas, candidatos, internos, vacantes o servicios..."
                style="flex:1; border:none; outline:none; font-size:15px; color:#1e293b; background:transparent;">
            <kbd style="background:#f1f5f9; color:#64748b; font-size:11px; padding:2px 6px; border-radius:4px;">Esc</kbd>
        </div>

        {{-- Resultados --}}
        <div id="rh-buscador-resultados" style="max-height:55vh; overflow-y:auto;"></div>
    </div>
</div>

<style>
    #rh-buscador .rh-busc-info { padding:32px 18px; text-align:center; color:#94a3b8; font-size:13px; }
    #rh-buscador .rh-busc-item { display:flex; align-items:center; gap:12px; padding:11px 18px; text-decoration:none; color:#1e293b; border-bottom:1px solid #f1f5f9; }
    #rh-buscador .rh-busc-item.activo { background:#eff6ff; }
    #rh-buscador .rh-busc-av { width:36px; height:36px; border-radius:50%; object-fit:cover; flex-shrink:0; }
    #rh-buscador .rh-busc-ic { width:36px; height:36px; border-radius:50%; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    #rh-buscador .rh-busc-main { flex:1; min-width:0; }
    #rh-buscador .rh-busc-tit { display:block; font-size:14px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    #rh-buscador .rh-busc-sub { display:block; font-size:12px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    #rh-buscador .rh-busc-meta { text-align:right; flex-shrink:0; }
    #rh-buscador .rh-busc-tipo { display:block; font-size:11px; color:#94a3b8; }
    #rh-buscador .rh-busc-est { display:block; font-size:11px; color:#475569; }
</style>

<script>
window.rhBuscador = window.rhBuscador || (function () {
    const URL_BUSCAR = @json(route('admin.buscar.json'));
    let el, input, cont, items = [], idx = 0, ctrl = null, tmr = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, c => (
            { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
        ));
    }

    function refs() {
        el = document.getElementById('rh-buscador');
        input = document.getElementById('rh-buscador-input');
        cont = document.getElementById('rh-buscador-resultados');
    }

    function pintar() {
        if (!cont) return;
        const q = (input.value || '').trim();
        if (q.length < 2) {
            cont.innerHTML = '<div class="rh-busc-info">Escribe al menos 2 letras para buscar.</div>';
            return;
        }
        if (items.length === 0) {
            cont.innerHTML = '<div class="rh-busc-info">No encontramos nada con “' + esc(q) + '”.</div>';
            return;
        }
        cont.innerHTML = items.map((it, i) =>
            '<a href="' + esc(it.url) + '" class="rh-busc-item' + (i === idx ? ' activo' : '') + '" data-i="' + i + '">'
            + (it.avatar
                ? '<img src="' + esc(it.avatar) + '" alt="" class="rh-busc-av">'
                : '<span class="rh-busc-ic">' + esc(it.icono) + '</span>')
            + '<span class="rh-busc-main"><span class="rh-busc-tit">' + esc(it.titulo) + '</span>'
            + '<span class="rh-busc-sub">' + esc(it.sub) + '</span></span>'
            + '<span class="rh-busc-meta"><span class="rh-busc-tipo">' + esc(it.tipo) + '</span>'
            + '<span class="rh-busc-est">' + esc(it.estado) + '</span></span>'
            + '</a>'
        ).join('');
    }

    function buscar() {
        idx = 0;
        clearTimeout(tmr);
        const q = (input.value || '').trim();
        if (q.length < 2) { items = []; pintar(); return; }
        tmr = setTimeout(() => {
            if (ctrl) ctrl.abort();
            ctrl = new AbortController();
            cont.innerHTML = '<div class="rh-busc-info">Buscando…</div>';
            fetch(URL_BUSCAR + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' },
                signal: ctrl.signal,
            })
                .then(r => r.json())
                .then(d => { items = d.resultados || []; pintar(); })
                .catch(e => { if (e.name !== 'AbortError') cont.innerHTML = '<div class="rh-busc-info">Error al buscar. Intenta de nuevo.</div>'; });
        }, 250);
    }

    function marcarActivo() {
        if (!cont) return;
        cont.querySelectorAll('.rh-busc-item').forEach((nodo, i) => {
            nodo.classList.toggle('activo', i === idx);
        });
    }

    function mover(paso) {
        if (items.length === 0) return;
        idx = (idx + paso + items.length) % items.length;
        marcarActivo();
        const activo = cont.querySelector('.rh-busc-item.activo');
        if (activo) activo.scrollIntoView({ block: 'nearest' });
    }

    function abrir() {
        refs();
        if (!el) return;
        el.style.display = 'flex';
        input.value = '';
        items = []; idx = 0;
        pintar();
        setTimeout(() => input.focus(), 30);
    }

    function cerrar() {
        if (el) el.style.display = 'none';
    }

    // Eventos (se registran una sola vez)
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            abrir();
        } else if (e.key === 'Escape') {
            cerrar();
        }
    });

    document.addEventListener('input', e => {
        if (e.target && e.target.id === 'rh-buscador-input') buscar();
    });

    document.addEventListener('keydown', e => {
        if (!el || el.style.display === 'none') return;
        if (e.target && e.target.id === 'rh-buscador-input') {
            if (e.key === 'ArrowDown') { e.preventDefault(); mover(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); mover(-1); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                if (items[idx]) window.location.href = items[idx].url;
            }
        }
    });

    document.addEventListener('mouseover', e => {
        const item = e.target.closest && e.target.closest('#rh-buscador .rh-busc-item');
        if (item) { idx = parseInt(item.dataset.i, 10) || 0; marcarActivo(); }
    });

    // Cerrar al hacer clic fuera de la caja
    document.addEventListener('click', e => {
        if (el && el.style.display !== 'none' && e.target === el) cerrar();
    });

    return { abrir, cerrar };
})();
</script>
