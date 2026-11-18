@extends('layouts.dashboard-layout')

@section('title', "Tableau de bord - SIOKA")

@section('content')

<style>
    /* Page L'équipe — uniquement les tokens et la police de la charte SIOKA (app.css) */
    .team-page { display:flex; flex-direction:column; gap:20px; font-family:var(--font-main); color:var(--blanc); }
    .team-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
    .team-sub { font-size:13px; font-weight:300; color:var(--plomb); margin-top:6px; }

    .team-toolbar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; justify-content:space-between; }
    .team-toolbar .pills-bar { margin-bottom:0; }

    .team-input { width:100%; background:var(--ink-3); border:1px solid var(--glass-bord); border-radius:var(--r-sm); padding:12px 16px; font-size:14px; font-family:var(--font-main); color:var(--blanc); outline:none; transition:border-color .25s; }
    .team-input:focus { border-color:var(--bleu); }
    .team-input::placeholder { color:var(--plomb); }
    .team-toolbar .team-input { flex:1; min-width:220px; max-width:360px; width:auto; }
    select.team-input { appearance:none; cursor:pointer; }
    select.team-input:disabled, .team-input:disabled { opacity:.45; cursor:not-allowed; }

    .team-btn-ghost { background:var(--blanc-doux); border:1px solid var(--glass-bord); color:var(--blanc); border-radius:var(--r-sm); padding:14px 24px; font-size:14px; font-weight:600; transition:background .2s; }
    .team-btn-ghost:hover { background:var(--blanc-mid); }

    /* Grille de cartes (le fond/bordure viennent de .panel) */
    .team-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:var(--gap); }
    .team-card { position:relative; display:flex; flex-direction:column; align-items:center; text-align:center; gap:4px; padding:28px 16px 18px; transition:transform .2s var(--ease), border-color .2s; }
    .team-card:hover { transform:translateY(-3px); border-color:var(--bleu); }
    .team-avatar { width:92px; height:92px; flex-shrink:0; border-radius:50%; margin-bottom:10px; display:grid; place-items:center; font-weight:700; font-size:26px; color:#fff; background:var(--magenta) center/cover no-repeat; box-shadow:0 0 0 3px var(--glass-bord); }
    .team-name { font-weight:700; font-size:15px; color:var(--blanc); }
    .team-role { font-size:12px; font-weight:500; color:var(--bleu); }
    .team-mail { font-size:12px; font-weight:300; color:var(--plomb); max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .team-tag { margin-top:8px; font-size:10px; font-weight:600; letter-spacing:.08em; text-transform:uppercase; padding:3px 10px; border-radius:999px; background:var(--bleu-glow); color:var(--bleu); }
    .team-tag.off { background:var(--blanc-doux); color:var(--plomb); }

    .team-actions { position:absolute; top:10px; right:10px; display:flex; gap:6px; opacity:0; transition:opacity .2s; }
    .team-card:hover .team-actions, .team-card:focus-within .team-actions { opacity:1; }
    .team-actions button { width:32px; height:32px; border-radius:var(--r-sm); border:1px solid var(--glass-bord); background:var(--ink-3); color:var(--blanc); font-size:14px; transition:background .2s; }
    .team-actions button:hover { background:var(--bleu); color:#fff; }
    .team-actions button[data-act="del"]:hover { background:#e5384f; }

    .team-grid.is-list { grid-template-columns:1fr; gap:10px; }
    .team-grid.is-list .team-card { flex-direction:row; text-align:left; gap:16px; padding:12px 18px; }
    .team-grid.is-list .team-avatar { width:48px; height:48px; font-size:16px; margin:0; }
    .team-grid.is-list .team-info { flex:1; min-width:0; }
    .team-grid.is-list .team-tag { margin:0; }
    .team-grid.is-list .team-actions { position:static; opacity:1; }

    .team-add-card { background:transparent; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; min-height:200px; border:2px dashed var(--glass-bord); border-radius:var(--r-lg); color:var(--plomb); font-family:var(--font-main); font-size:14px; transition:color .2s, border-color .2s; }
    .team-add-card:hover { color:var(--blanc); border-color:var(--magenta); }
    .team-add-card span { font-size:32px; line-height:1; }
    .team-grid.is-list .team-add-card { min-height:56px; flex-direction:row; }
    .team-empty { text-align:center; padding:40px; font-size:14px; color:var(--plomb); }

    /* Organigramme en pyramide : photos rondes, nom en capitales, fonction colorée,
       traits épais à angle droit vers les subordonnés */
    .org-board { display:flex; align-items:flex-start; gap:20px; }
    .org-scroll { flex:1; min-width:0; overflow-x:auto; padding:16px 4px 28px; }
    .org-tree { --org-line:color-mix(in srgb, color-mix(in srgb, var(--bleu) 50%, var(--magenta)) 60%, var(--blanc)); --org-a:22px; --org-b:40px; --org-w:3px; display:flex; justify-content:center; min-width:max-content; }
    :root[data-theme="light"] .org-tree { --org-line:color-mix(in srgb, color-mix(in srgb, var(--bleu) 50%, var(--magenta)) 55%, var(--ink)); }
    .org-tree ul { display:flex; justify-content:center; list-style:none; padding-top:var(--org-a); position:relative; }
    .org-tree > ul { padding-top:0; }
    .org-tree li { position:relative; display:flex; flex-direction:column; align-items:center; padding:var(--org-b) 8px 0; }

    .org-tree ul ul { margin:0 -8px; }

    /* Barre horizontale reliant les frères (le premier et le dernier ne dessinent que leur moitié) */
    .org-tree li::before, .org-tree li::after { content:''; position:absolute; top:0; width:calc(50% + var(--org-w) / 2); border-top:var(--org-w) solid var(--org-line); }
    .org-tree li::before { right:calc(50% - var(--org-w) / 2); }
    .org-tree li::after { left:calc(50% - var(--org-w) / 2); }
    .org-tree li:first-child::before, .org-tree li:last-child::after,
    .org-tree li:only-child::before, .org-tree li:only-child::after { display:none; }

    /* Trait vertical du responsable vers la barre */
    .org-tree ul ul::before { content:''; position:absolute; top:0; left:50%; width:var(--org-w); height:var(--org-a); background:var(--org-line); transform:translateX(-50%); }

    .org-tree > ul > li { padding-top:0; }
    .org-tree > ul > li::before, .org-tree > ul > li::after { display:none; }

    .org-node { position:relative; display:flex; flex-direction:column; align-items:center; gap:3px; width:156px; text-align:center; }
    /* Trait vertical vers le subordonné (comme l'image : traits seuls, sans pointe) */
    .org-node::before { content:''; position:absolute; left:50%; top:calc(var(--org-b) * -1); width:var(--org-w); height:var(--org-b); background:var(--org-line); transform:translateX(-50%); }
    .org-tree > ul > li > .org-node::before { display:none; }

    .org-node .team-avatar { width:86px; height:86px; font-size:25px; margin-bottom:8px; box-shadow:0 0 0 3px var(--ink), 0 0 0 4.5px var(--plomb); transition:transform .2s var(--ease), box-shadow .2s; }
    .org-node:hover .team-avatar { transform:scale(1.06); box-shadow:0 0 0 3px var(--ink), 0 0 0 4.5px var(--bleu); }
    .org-node.lvl0 .team-avatar { width:100px; height:100px; font-size:29px; box-shadow:0 0 0 3px var(--ink), 0 0 0 4.5px var(--magenta); }
    .org-node strong { display:block; font-size:13px; font-weight:400; letter-spacing:.02em; text-transform:uppercase; color:var(--blanc); }
    .org-node .r { display:block; font-size:13px; font-weight:400; line-height:1.35; color:var(--bleu); }
    .org-node.lvl0 .r { color:var(--magenta); }
    .org-node .m { display:block; max-width:100%; font-size:12px; font-weight:300; line-height:1.35; color:var(--plomb); overflow-wrap:anywhere; }

    /* Modale */
    .team-overlay { position:fixed; inset:0; z-index:1000; display:none; align-items:center; justify-content:center; padding:16px; background:rgba(0,0,0,.6); backdrop-filter:blur(4px); }
    .team-overlay.show { display:flex; }
    .team-modal { width:100%; max-width:460px; max-height:92vh; overflow:auto; border-radius:var(--r-xl); border:1px solid var(--glass-bord); background:var(--ink-2); padding:26px; box-shadow:0 30px 60px -20px rgba(0,0,0,.6); animation:teamPop .22s var(--ease-out); }
    @keyframes teamPop { from { opacity:0; transform:translateY(12px) scale(.97); } }
    .team-modal h3 { font-size:18px; font-weight:800; margin-bottom:18px; color:var(--blanc); display:flex; align-items:center; gap:12px; }
    .team-modal h3::before { content:''; width:6px; height:22px; background:var(--magenta); border-radius:4px; }
    .team-drop { display:flex; flex-direction:column; align-items:center; gap:8px; padding:18px; border:2px dashed var(--glass-bord); border-radius:var(--r-lg); cursor:pointer; transition:border-color .2s, background .2s; margin-bottom:6px; }
    .team-drop:hover, .team-drop.drag { border-color:var(--bleu); background:var(--bleu-glow); }
    .team-drop strong { font-size:13px; font-weight:600; }
    .team-drop small { font-size:12px; font-weight:300; color:var(--plomb); }
    .team-drop .team-avatar { margin:0; width:104px; height:104px; }
    .team-remove-photo { display:none; margin:0 auto 12px; font-size:12px; font-weight:600; color:var(--magenta); }
    .team-remove-photo.show { display:block; }
    .team-field { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
    .team-field > label { font-size:12px; font-weight:600; color:var(--plomb); }
    .team-section { margin:18px 0 14px; padding-top:16px; border-top:1px solid var(--glass-bord); }
    .team-section-title { font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--blanc); margin-bottom:12px; }
    .team-switch { display:flex; align-items:center; gap:12px; margin-bottom:14px; cursor:pointer; font-size:14px; }
    .team-switch input { position:absolute; opacity:0; pointer-events:none; }
    .team-switch i { position:relative; width:42px; height:24px; flex-shrink:0; border-radius:999px; background:var(--ink-4); transition:background .2s; }
    .team-switch i::after { content:''; position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s var(--ease); }
    .team-switch input:checked + i { background:var(--bleu); }
    .team-switch input:checked + i::after { transform:translateX(18px); }
    .team-switch input:focus-visible + i { box-shadow:0 0 0 3px var(--bleu-glow); }
    .team-foot { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
    .team-error { font-size:12px; color:#e5384f; min-height:16px; margin-bottom:4px; }

    @media (max-width:600px) { .team-grid:not(.is-list) { grid-template-columns:repeat(2, 1fr); gap:12px; } .team-card { padding:22px 10px 14px; } }
</style>

<div class="team-page">

    <div class="team-head">
        <div>
            <h2 class="sec-title">L'équipe</h2>
            <p class="team-sub">Gérez les membres affichés sur la page publique et l'organigramme de SIOKA.</p>
        </div>
        <button type="button" class="btn-submit" id="teamAdd">+ Ajouter un membre</button>
    </div>

    <div class="pills-bar" role="tablist" id="teamTabs">
        <button type="button" class="pill-btn on" data-tab="org">Organigramme</button>
        <button type="button" class="pill-btn" data-tab="members">Membres (<span id="teamCount">0</span>)</button>
    </div>

    {{-- ── Onglet Membres ── --}}
    <section id="tabMembers" hidden>
        <div class="team-toolbar">
            <input type="search" class="team-input" id="teamSearch" placeholder="Rechercher un nom, une fonction, un e-mail…">
            <div class="pills-bar" id="teamView">
                <button type="button" class="pill-btn on" data-view="grid">Grille</button>
                <button type="button" class="pill-btn" data-view="list">Liste</button>
            </div>
        </div>
        <div class="team-grid" id="teamGrid" style="margin-top:20px"></div>
        <div class="team-empty" id="teamEmpty" hidden>Aucun membre ne correspond à votre recherche.</div>
    </section>

    {{-- ── Onglet Organigramme (généré à partir des membres cochés « Afficher dans l'organigramme ») ── --}}
    <section id="tabOrg">
        
        <div class="org-board">
            <div class="org-scroll"><div class="org-tree" id="orgTree"></div></div>
        </div>
    </section>

</div>

{{-- ── Modale ajout / modification ── --}}
<div class="team-overlay" id="teamOverlay">
    <div class="team-modal" role="dialog" aria-modal="true" aria-labelledby="teamModalTitle">
        <h3 id="teamModalTitle">Ajouter un membre</h3>

        <label class="team-drop" id="teamDrop">
            <div class="team-avatar" id="teamPreview">?</div>
            <strong>Choisir une photo de profil</strong>
            <small>JPG, PNG ou WebP — glisser-déposer possible</small>
            <input type="file" id="teamFile" accept="image/*" hidden>
        </label>
        <button type="button" class="team-remove-photo" id="teamRemovePhoto">Retirer la photo</button>

        <div class="team-field">
            <label for="teamNameInput">Nom complet</label>
            <input type="text" class="team-input" id="teamNameInput" placeholder="Ex. Hanta Rasoamanana" autocomplete="off">
        </div>
        <div class="team-field">
            <label for="teamRoleInput">Fonction</label>
            <input type="text" class="team-input" id="teamRoleInput" placeholder="Ex. Rédactrice en chef" autocomplete="off">
        </div>
        <div class="team-field">
            <label for="teamMailInput">Adresse e-mail</label>
            <input type="email" class="team-input" id="teamMailInput" placeholder="prenom.nom&#64;sioka.org" autocomplete="off">
        </div>

        <div class="team-section">
            <div class="team-section-title">Organigramme</div>
            <label class="team-switch">
                <input type="checkbox" id="teamOrgInput">
                <i></i>
                <span>Afficher dans l'organigramme</span>
            </label>
            <div class="team-field" style="margin-bottom:0">
                <label for="teamParentInput">Rattaché à (supérieur hiérarchique)</label>
                <select class="team-input" id="teamParentInput"></select>
            </div>
        </div>

        <div class="team-error" id="teamError"></div>
        <div class="team-foot">
            <button type="button" class="team-btn-ghost" id="teamCancel">Annuler</button>
            <button type="button" class="btn-submit" id="teamSave">Enregistrer</button>
        </div>
    </div>
</div>

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

@push('scripts')
<script>
    // ── Page "L'équipe" : version dynamique (enregistrée en base) ──
    (function () {
        const page = document.querySelector('.team-page');
        if (!page) return;

        const CSRF = '{{ csrf_token() }}';
        const URL_STORE = @json(route('lequipe.store'));
        const URL_ITEM = @json(route('lequipe.update', ['equipe' => '__ID__']));
        const urlFor = (id) => URL_ITEM.replace('__ID__', id);

        let members = @json($members);
        let editingId = null;
        let currentPhoto = null;   // aperçu (URL ou data URL)
        let currentFile = null;    // nouveau fichier à envoyer
        let removePhoto = false;   // photo existante à retirer

        const $ = (id) => document.getElementById(id);
        const grid = $('teamGrid'), count = $('teamCount'), empty = $('teamEmpty'), search = $('teamSearch');
        const overlay = $('teamOverlay'), nameIn = $('teamNameInput'), roleIn = $('teamRoleInput'), mailIn = $('teamMailInput');
        const orgIn = $('teamOrgInput'), parentIn = $('teamParentInput'), errorEl = $('teamError');
        const preview = $('teamPreview'), fileIn = $('teamFile'), drop = $('teamDrop'), removeBtn = $('teamRemovePhoto');
        const saveBtn = $('teamSave');

        const initials = (n) => n.trim().split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '?';

        function el(tag, cls, text) {
            const e = document.createElement(tag);
            if (cls) e.className = cls;
            if (text !== undefined) e.textContent = text;
            return e;
        }

        function setAvatar(node, photo, name) {
            if (photo) { node.style.backgroundImage = 'url("' + photo + '")'; node.textContent = ''; }
            else { node.style.backgroundImage = ''; node.textContent = initials(name || ''); }
        }

        function descendants(id) {
            const out = new Set();
            (function walk(p) { members.filter(m => m.parent === p).forEach(m => { out.add(m.id); walk(m.id); }); })(id);
            return out;
        }

        // ── Requête serveur (POST + _method pour supporter l'envoi de fichier) ──
        async function send(url, method, formData) {
            if (method !== 'POST') formData.append('_method', method);
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: formData
            });
            if (res.status === 204) return null;
            const json = await res.json().catch(() => ({}));
            if (!res.ok) {
                const first = json.errors ? Object.values(json.errors)[0][0] : null;
                throw new Error(first || json.message || 'Une erreur est survenue.');
            }
            return json;
        }

        // ── Liste des membres ──
        function render() {
            const q = search.value.trim().toLowerCase();
            const list = members.filter(m => (m.name + ' ' + m.role + ' ' + (m.email || '')).toLowerCase().includes(q));
            grid.innerHTML = '';

            list.forEach(m => {
                const card = el('article', 'panel team-card');
                const av = el('div', 'team-avatar');
                setAvatar(av, m.photo, m.name);

                const info = el('div', 'team-info');
                info.append(el('div', 'team-name', m.name), el('div', 'team-role', m.role));
                if (m.email) info.append(el('div', 'team-mail', m.email));

                const tag = el('span', 'team-tag' + (m.org ? '' : ' off'), m.org ? 'Dans l\'organigramme' : 'Hors organigramme');

                const actions = el('div', 'team-actions');
                const edit = el('button', '', '✎'); edit.type = 'button'; edit.title = 'Modifier'; edit.dataset.act = 'edit';
                const del = el('button', '', '🗑'); del.type = 'button'; del.title = 'Supprimer'; del.dataset.act = 'del';
                edit.onclick = () => openModal(m.id);
                del.onclick = async () => {
                    if (!confirm('Supprimer ' + m.name + ' de l\'équipe ?')) return;
                    try {
                        await send(urlFor(m.id), 'DELETE', new FormData());
                    } catch (err) {
                        alert(err.message);
                        return;
                    }
                    members.forEach(x => { if (x.parent === m.id) x.parent = null; });
                    members = members.filter(x => x.id !== m.id);
                    render();
                };
                actions.append(edit, del);

                card.append(av, info, tag, actions);
                grid.append(card);
            });

            if (!q) {
                const add = el('button', 'team-add-card');
                add.type = 'button';
                add.append(el('span', '', '+'), el('strong', '', 'Ajouter un membre'));
                add.onclick = () => openModal(null);
                grid.append(add);
            }

            count.textContent = members.length;
            empty.hidden = list.length > 0 || !q;
            renderOrg();
        }

        // ── Organigramme ──
        const orgKids = (m) => members.filter(k => k.org && k.parent === m.id);
        function orgNode(m, depth) {
            const li = el('li');
            const box = el('div', 'org-node' + (depth === 0 ? ' lvl0' : ''));
            const av = el('div', 'team-avatar');
            setAvatar(av, m.photo, m.name);
            const txt = el('div', 'org-txt');
            txt.append(el('strong', '', m.name), el('span', 'r', m.role));
            if (m.email) txt.append(el('span', 'm', m.email));
            box.append(av, txt);
            box.style.cursor = 'pointer';
            box.title = 'Modifier ' + m.name;
            box.onclick = () => openModal(m.id);
            li.append(box);
            const kids = orgKids(m);
            if (kids.length) {
                const ul = el('ul');
                kids.forEach(k => ul.append(orgNode(k, depth + 1)));
                li.append(ul);
            }
            return li;
        }
        // Réduit l'organigramme pour qu'il tienne dans la largeur disponible (défilement sinon)
        function fitOrg() {
            const box = $('orgTree'), wrap = box.parentElement;
            box.style.zoom = 1;
            const tree = box.firstElementChild;
            if (!tree || !wrap.clientWidth) return;
            const need = tree.offsetWidth, avail = wrap.clientWidth - 8;
            box.style.zoom = need > avail ? Math.max(0.8, avail / need) : 1;
        }
        function renderOrg() {
            const box = $('orgTree');
            box.innerHTML = '';
            const shown = members.filter(m => m.org);
            const ids = new Set(shown.map(m => m.id));
            const roots = shown.filter(m => m.parent == null || !ids.has(m.parent));
            if (!roots.length) { box.append(el('p', 'team-empty', 'Aucun membre dans l\'organigramme.')); return; }
            const ul = el('ul');
            roots.forEach(r => ul.append(orgNode(r, 0)));
            box.append(ul);
            fitOrg();
        }

        // ── Modale ──
        function fillParents(selected) {
            const banned = editingId ? descendants(editingId) : new Set();
            parentIn.innerHTML = '';
            const none = el('option', '', '— Aucun (sommet de l\'organigramme) —'); none.value = '';
            parentIn.append(none);
            members.filter(m => m.id !== editingId && !banned.has(m.id)).forEach(m => {
                const o = el('option', '', m.name + ' — ' + m.role); o.value = m.id;
                parentIn.append(o);
            });
            parentIn.value = selected == null ? '' : String(selected);
        }
        function syncOrgState() { parentIn.disabled = !orgIn.checked; }

        function openModal(id) {
            editingId = id;
            const m = members.find(x => x.id === id);
            $('teamModalTitle').textContent = m ? 'Modifier le membre' : 'Ajouter un membre';
            nameIn.value = m ? m.name : '';
            roleIn.value = m ? m.role : '';
            mailIn.value = m ? (m.email || '') : '';
            orgIn.checked = m ? m.org : true;
            currentPhoto = m ? m.photo : null;
            currentFile = null;
            removePhoto = false;
            fileIn.value = '';
            errorEl.textContent = '';
            fillParents(m ? m.parent : null);
            syncOrgState();
            refreshPreview();
            overlay.classList.add('show');
            setTimeout(() => nameIn.focus(), 50);
        }
        function closeModal() { overlay.classList.remove('show'); }

        function refreshPreview() {
            setAvatar(preview, currentPhoto, nameIn.value);
            removeBtn.classList.toggle('show', !!currentPhoto);
        }
        function loadFile(file) {
            if (!file || !file.type.startsWith('image/')) return;
            currentFile = file;
            removePhoto = false;
            const r = new FileReader();
            r.onload = () => { currentPhoto = r.result; refreshPreview(); };
            r.readAsDataURL(file);
        }

        fileIn.addEventListener('change', () => loadFile(fileIn.files[0]));
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('drag'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('drag'); }));
        drop.addEventListener('drop', e => loadFile(e.dataTransfer.files[0]));
        removeBtn.onclick = () => { currentPhoto = null; currentFile = null; removePhoto = true; fileIn.value = ''; refreshPreview(); };
        nameIn.addEventListener('input', () => { if (!currentPhoto) refreshPreview(); });
        orgIn.addEventListener('change', syncOrgState);

        saveBtn.onclick = async () => {
            const name = nameIn.value.trim(), role = roleIn.value.trim(), email = mailIn.value.trim();
            if (!name) { errorEl.textContent = 'Le nom complet est obligatoire.'; nameIn.focus(); return; }
            if (!role) { errorEl.textContent = 'La fonction est obligatoire.'; roleIn.focus(); return; }
            if (email && !/^[^\s\x40]+\x40[^\s\x40]+\.[^\s\x40]+$/.test(email)) { errorEl.textContent = 'Adresse e-mail invalide.'; mailIn.focus(); return; }

            const fd = new FormData();
            fd.append('name', name);
            fd.append('role', role);
            fd.append('email', email);
            fd.append('org', orgIn.checked ? '1' : '0');
            fd.append('parent_id', parentIn.value);
            if (currentFile) fd.append('photo', currentFile);
            else if (removePhoto) fd.append('remove_photo', '1');

            saveBtn.disabled = true;
            errorEl.textContent = '';
            try {
                if (editingId) {
                    const saved = await send(urlFor(editingId), 'PUT', fd);
                    Object.assign(members.find(x => x.id === editingId), saved);
                } else {
                    const saved = await send(URL_STORE, 'POST', fd);
                    members.push(saved);
                }
                closeModal();
                render();
            } catch (err) {
                errorEl.textContent = err.message;
            } finally {
                saveBtn.disabled = false;
            }
        };
        $('teamCancel').onclick = closeModal;
        overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
        $('teamAdd').onclick = () => openModal(null);
        search.addEventListener('input', render);

        // Bascule grille / liste
        $('teamView').querySelectorAll('button').forEach(b => b.onclick = () => {
            $('teamView').querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
            grid.classList.toggle('is-list', b.dataset.view === 'list');
        });

        // Onglets Membres / Organigramme
        $('teamTabs').querySelectorAll('button').forEach(b => b.onclick = () => {
            $('teamTabs').querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
            $('tabMembers').hidden = b.dataset.tab !== 'members';
            $('tabOrg').hidden = b.dataset.tab !== 'org';
            if (b.dataset.tab === 'org') fitOrg();
        });

        window.addEventListener('resize', fitOrg);
        render();
    })();
</script>
@endpush