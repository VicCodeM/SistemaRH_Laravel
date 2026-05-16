<div style="padding:28px;">
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:20px;">
        <div style="width:44px;height:44px;border-radius:12px;background:var(--danger-light);color:var(--danger);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:22px;height:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <div>
            <h2 style="margin:0;font-size:1.1rem;font-weight:700;">Rechazar solicitud de candidato</h2>
            <p style="margin:4px 0 0;color:#64748b;font-size:0.88rem;">La solicitud quedará como <strong>Rechazada</strong> y el candidato no podrá postularse a vacantes.</p>
        </div>
    </div>

    <div style="padding:14px 16px;border-radius:10px;background:#f8fafc;border:1px solid var(--border);margin-bottom:20px;">
        <p style="margin:0;font-size:0.95rem;font-weight:600;color:var(--text);">{{ $candidato->nombreCompleto() }}</p>
        <p style="margin:4px 0 0;font-size:0.83rem;color:#64748b;">{{ $candidato->puesto_deseado ?: $candidato->usuario?->email }}</p>
    </div>

    <p style="margin:0 0 20px;color:#475569;font-size:0.9rem;">¿Confirmas el rechazo? El candidato seguirá registrado en el sistema pero sin acceso al proceso de selección.</p>

    <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="rhModalClose()" class="btn btn-secondary">Cancelar</button>
        <form method="POST" action="{{ route('admin.candidatos.rechazar', $candidato) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-danger">Confirmar rechazo</button>
        </form>
    </div>
</div>
