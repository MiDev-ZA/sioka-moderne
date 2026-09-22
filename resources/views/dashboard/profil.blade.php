@extends('layouts.dashboard-layout')

@section('title', "Tableau de bord - SIOKA")

@section('content')

{{-- Eto no apetraka ireo contenus sy sections mifanaraka amin'ny pejy sy menus --}}

@endsection

@push('scripts')

<script>
    // ── Carte "Statistiques de visite" : version avancée ──────────
    (function () {
        const panel = document.getElementById('visitsPanel');
        if (!panel) return;

        const fmt = new Intl.NumberFormat('fr-FR');
        const totalEl = document.getElementById('visitsTotal');
        const deltaEl = document.getElementById('visitsDelta');
        const periodLabelEl = document.getElementById('visitsPeriodLabel');
        const trend = document.getElementById('visitsTrend');
        const tooltip = document.getElementById('visitsTooltip');
        const wrap = panel.querySelector('.visits-chart-wrap');
        const daysSet = document.getElementById('daysSet');
        const monthsSet = document.getElementById('monthsSet');
        const allGroups = panel.querySelectorAll('.bar-group');
        const periodButtons = panel.querySelectorAll('.visits-period button');

        function activeGroups(period) {
            return period === 'year' ? monthsSet.querySelectorAll('.bar-group') : daysSet.querySelectorAll('.bar-group');
        }

        // Compteur animé (ease-out)
        function animateNumber(el, from, to, duration) {
            const start = performance.now();
            function tick(now) {
                const p = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = fmt.format(Math.round(from + (to - from) * eased));
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }

        // Recalcule la courbe de tendance à partir du sommet des barres "pages vues" du jeu actif
        function refreshTrend(period) {
            const pts = Array.from(activeGroups(period)).map(g => {
                const bp = g.querySelector('.bar-p');
                const x = parseFloat(bp.getAttribute('x')) + parseFloat(bp.getAttribute('width')) / 2;
                const y = parseFloat(bp.getAttribute('y'));
                return x + ',' + y;
            });
            trend.setAttribute('points', pts.join(' '));
        }

        function setPeriod(period, animateCount) {
            const isYear = period === 'year';
            daysSet.style.display = isYear ? 'none' : '';
            monthsSet.style.display = isYear ? '' : 'none';

            activeGroups(period).forEach(g => {
                ['u', 'p'].forEach(k => {
                    const rect = g.querySelector('.bar-' + k);
                    rect.setAttribute('y', rect.getAttribute('data-y' + period));
                    rect.setAttribute('height', rect.getAttribute('data-h' + period));
                });
            });

            const targetTotal = parseInt(totalEl.getAttribute('data-t' + period), 10);
            const currentTotal = parseInt((totalEl.textContent || '0').replace(/\s/g, ''), 10) || 0;
            if (animateCount) animateNumber(totalEl, currentTotal, targetTotal, 700);
            else totalEl.textContent = fmt.format(targetTotal);

            deltaEl.textContent = deltaEl.getAttribute('data-d' + period);
            periodLabelEl.textContent = period === '7' ? 'cette semaine'
                : period === '30' ? 'ces 30 derniers jours'
                : "sur l'année dernière";

            // La courbe se redessine en douceur après la transition des barres
            setTimeout(() => refreshTrend(period), isYear ? 0 : 350);
            // Ré-anime l'entrée des barres du jeu qui vient d'apparaître
            if (isYear) {
                panel.classList.remove('is-ready');
                requestAnimationFrame(() => requestAnimationFrame(() => panel.classList.add('is-ready')));
            }
        }

        periodButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('on')) return;
                periodButtons.forEach(b => b.classList.remove('on'));
                btn.classList.add('on');
                setPeriod(btn.getAttribute('data-period'), true);
            });
        });

        // Tooltip interactif au survol des groupes de barres (jours ou mois)
        allGroups.forEach(g => {
            g.addEventListener('mouseenter', () => {
                const currentPeriod = panel.querySelector('.visits-period button.on').getAttribute('data-period');
                activeGroups(currentPeriod).forEach(o => o.classList.toggle('dim', o !== g));
                const u = fmt.format(parseInt(g.getAttribute('data-u' + currentPeriod), 10));
                const p = fmt.format(parseInt(g.getAttribute('data-p' + currentPeriod), 10));
                tooltip.innerHTML = '<strong>' + g.getAttribute('data-day') + '</strong>' +
                    '<span><i style="background:var(--bleu)"></i>Uniques : ' + u + '</span>' +
                    '<span><i style="background:var(--magenta)"></i>Pages vues : ' + p + '</span>';
                tooltip.classList.add('show');
            });
            g.addEventListener('mousemove', (e) => {
                const box = wrap.getBoundingClientRect();
                let left = e.clientX - box.left + 14;
                let top = e.clientY - box.top - 10;
                const maxLeft = box.width - 170;
                if (left > maxLeft) left = e.clientX - box.left - 170 - 14;
                tooltip.style.left = left + 'px';
                tooltip.style.top = top + 'px';
            });
            g.addEventListener('mouseleave', () => {
                const currentPeriod = panel.querySelector('.visits-period button.on').getAttribute('data-period');
                activeGroups(currentPeriod).forEach(o => o.classList.remove('dim'));
                tooltip.classList.remove('show');
            });
        });

        // Animation d'entrée + premier affichage
        requestAnimationFrame(() => panel.classList.add('is-ready'));
        refreshTrend('7');
        animateNumber(totalEl, 0, parseInt(totalEl.getAttribute('data-t7'), 10), 1100);
    })();
</script>
@endpush