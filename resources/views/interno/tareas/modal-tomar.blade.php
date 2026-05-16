{{-- Modal tomar tarea (tema light) --}}
<div style="font-family:inherit;">
    <div class="modal-header">
        <div>
            <h2 class="modal-title">Tomar tarea</h2>
            <p class="modal-subtitle">{{ $tarea->servicio?->nombre ?? 'Servicio' }} · {{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
        </div>
        <button onclick="rhModalClose()" class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
        <p style="margin:0;color:var(--text-secondary);font-size:13px;line-height:1.6;">
            Confirma que vas a tomar esta tarea para que cambie a <strong>En proceso</strong>.
        </p>
    </div>
    <div class="modal-footer" style="justify-content:flex-end;">
        <button onclick="rhModalClose()" class="btn btn-ghost">Cancelar</button>
        <form method="POST" action="{{ route('interno.tareas.tomar', $tarea) }}" style="margin:0;">@csrf @method('PATCH')
            <button type="submit" class="btn btn-primary">Tomar tarea</button>
        </form>
    </div>
</div>
