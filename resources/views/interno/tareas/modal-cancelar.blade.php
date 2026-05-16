{{-- Modal cancelar tarea (tema light) --}}
<div style="font-family:inherit;">
    <div class="modal-header">
        <div>
            <h2 class="modal-title">Cancelar tarea</h2>
            <p class="modal-subtitle">{{ $tarea->servicio?->nombre ?? 'Servicio' }} · {{ \App\Models\ServicioAsignado::asignableTipoLabel($tarea->asignable_type) }} · {{ $tarea->asignableNombre() }}</p>
        </div>
        <button onclick="rhModalClose()" class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
        <form method="POST" action="{{ route('interno.tareas.cancelar', $tarea) }}">
            @csrf @method('PATCH')
            <div class="form-group">
                <label class="form-label">Motivo de cancelación</label>
                <textarea name="cierre_resumen" rows="5" required maxlength="2000" class="form-input"
                    placeholder="Indica por qué no procede o por qué debe cancelarse...">{{ old('cierre_resumen') }}</textarea>
                @error('cierre_resumen')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;">
                <button type="button" onclick="rhModalClose()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-danger">Cancelar tarea</button>
            </div>
        </form>
    </div>
</div>
