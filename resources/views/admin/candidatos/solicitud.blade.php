<x-app-layout>
    <x-slot name="header">
        <nav class="breadcrumbs">
            <a href="{{ route('admin.candidatos') }}">Candidatos</a>
            <span class="breadcrumb-sep">&rsaquo;</span>
            <span>Solicitud</span>
        </nav>
        <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap;">
            <div>
                <h1 class="page-title">Solicitud de {{ $candidato->nombreCompleto() }}</h1>
                <p class="page-subtitle">Revisa la ficha completa y corrige lo necesario sin perder el avance.</p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('admin.candidatos.solicitud.pdf', $candidato) }}" target="_blank" class="btn btn-secondary" title="Descargar solicitud completa en PDF">📄 Descargar PDF</a>
                <a href="{{ route('admin.candidatos') }}" class="btn btn-secondary">Volver</a>
                <button onclick="rhModal('{{ route('admin.candidatos.modal', $candidato) }}')" class="btn btn-primary">Ver resumen</button>
            </div>
        </div>
    </x-slot>

    <div style="max-width:1180px; margin:0 auto;">
        <livewire:candidato-solicitud :candidato-id="$candidato->id" :modo-admin="true" />
    </div>
</x-app-layout>
