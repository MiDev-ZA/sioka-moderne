@if ($q !== '')
    <p class="nl-meta">{{ $abonnes->total() }} résultat(s) pour « {{ $q }} »</p>
@endif

@if ($abonnes->count() > 0)
    @if ($abonnes->hasPages())
        <div class="pagination-wrap pagination-wrap--top">{{ $abonnes->links() }}</div>
    @endif

    <div class="nl-table-wrap">
        <table class="nl-table">
            <thead>
                <tr>
                    <th class="nl-col-num">N°</th>
                    <th>E-mail</th>
                    <th>Date d'inscription</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($abonnes as $abonne)
                    <tr>
                        <td class="nl-col-num">{{ $abonnes->firstItem() + $loop->index }}</td>
                        <td class="nl-email">{{ $abonne->email }}</td>
                        <td class="row-date">{{ $abonne->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="row-actions">
                                <button type="button"
                                    class="row-icon-btn row-icon-btn--danger"
                                    title="Supprimer"
                                    data-delete-btn
                                    data-action="{{ route('abonnees-newsletter.destroy', $abonne) }}"
                                    data-email="{{ $abonne->email }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 7"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6"/></svg>
        @if ($q !== '')
            <strong>Aucun résultat</strong>
            <p>Aucun abonné ne correspond à « {{ $q }} ».</p>
        @else
            <strong>Aucun abonné pour le moment</strong>
            <p>Les adresses saisies dans le formulaire de la page d'accueil apparaîtront ici.</p>
        @endif
    </div>
@endif