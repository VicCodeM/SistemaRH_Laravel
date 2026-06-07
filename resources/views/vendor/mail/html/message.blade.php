@php
    $sitioService = app(\App\Services\SitioService::class);
    $logoUrl = $sitioService->logoUrl();
    $marca = \App\Services\SitioService::partirMarca(config('app.name', 'SistemaRH'));
@endphp
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
    <tr>
        <td style="padding-right: 12px;">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" style="display:block; width:60px; height:60px; border-radius:16px; object-fit:contain; background:#ffffff; border:1px solid #dbe7ff;">
            @else
                <div style="width:56px; height:56px; border-radius:16px; background:linear-gradient(135deg,#1d4ed8,#0f172a); color:#fff; font-size:18px; font-weight:800; line-height:56px; text-align:center; letter-spacing:.04em;">SRH</div>
            @endif
        </td>
        <td style="text-align:left; vertical-align:middle;">
            <div style="font-size:20px; line-height:1; font-weight:900; color:#0f172a; letter-spacing:-.4px;">
                {{ $marca['base'] }}<span style="color:#2563eb;">{{ $marca['acento'] }}</span>
            </div>
            <div style="margin-top:4px; font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:#64748b;">Notificaciones oficiales del sistema</div>
        </td>
    </tr>
</table>
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
