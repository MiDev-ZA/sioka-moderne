{{-- resources/views/dashboard/partials/ga-bar-list.blade.php
     Paramètres : $items = [['label' => string, 'value' => int, 'pct' => float], ...], $color = couleur CSS --}}
@php $barColor = $color ?? 'var(--bleu)'; @endphp

@forelse ($items as $item)
    <div style="margin-bottom:.85rem;">
        <div style="display:flex; justify-content:space-between; gap:.75rem; font-size:.88rem; margin-bottom:.3rem;">
            <span>{{ $item['label'] }}</span>
            <span style="color:var(--plomb);">{{ number_format($item['value'], 0, ',', ' ') }} · {{ number_format($item['pct'], 1, ',', ' ') }} %</span>
        </div>
        <div style="height:6px; border-radius:99px; background:var(--glass-bord); overflow:hidden;">
            <div style="width:{{ $item['pct'] }}%; height:100%; border-radius:99px; background:{{ $barColor }};"></div>
        </div>
    </div>
@empty
    <p style="color:var(--plomb);">Aucune donnée pour le moment.</p>
@endforelse