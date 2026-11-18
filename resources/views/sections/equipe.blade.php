@extends('layouts.app')

@section('title', "L'équipe - SIOKA")

@section('content')

<style>
    /* Page publique « L'équipe » : organigramme en lecture seule (tokens de la charte SIOKA uniquement) */
    /* Même conteneur et même espace sous le header que la page « Grille des Programmes » */
    .pub-team { display:flex; flex-direction:column; gap:20px; max-width:960px; margin:0 auto; padding:56px 16px 56px; font-family:var(--font-main); color:var(--blanc); }
    @media (max-width:640px) { .pub-team { padding-top:36px; } }
    .pub-team-sub { font-size:13px; font-weight:300; color:var(--plomb); margin-top:6px; }
    .pub-team-empty { text-align:center; padding:40px; font-size:14px; color:var(--plomb); }

    .team-avatar { width:92px; height:92px; flex-shrink:0; border-radius:50%; margin-bottom:10px; display:grid; place-items:center; font-weight:700; font-size:26px; color:#fff; background:var(--magenta) center/cover no-repeat; box-shadow:0 0 0 3px var(--glass-bord); }

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
    /* Trait vertical vers le subordonné (traits seuls, sans pointe) */
    .org-node::before { content:''; position:absolute; left:50%; top:calc(var(--org-b) * -1); width:var(--org-w); height:var(--org-b); background:var(--org-line); transform:translateX(-50%); }
    .org-tree > ul > li > .org-node::before { display:none; }

    .org-node .team-avatar { width:86px; height:86px; font-size:25px; margin-bottom:8px; box-shadow:0 0 0 3px var(--ink), 0 0 0 4.5px var(--plomb); transition:transform .2s var(--ease), box-shadow .2s; }
    .org-node:hover .team-avatar { transform:scale(1.06); box-shadow:0 0 0 3px var(--ink), 0 0 0 4.5px var(--bleu); }
    .org-node.lvl0 .team-avatar { width:100px; height:100px; font-size:29px; box-shadow:0 0 0 3px var(--ink), 0 0 0 4.5px var(--magenta); }
    .org-node strong { display:block; font-size:13px; font-weight:400; letter-spacing:.02em; text-transform:uppercase; color:var(--blanc); }
    .org-node .r { display:block; font-size:13px; font-weight:400; line-height:1.35; color:var(--bleu); }
    .org-node.lvl0 .r { color:var(--magenta); }
    .org-node .m { display:block; max-width:100%; font-size:12px; font-weight:300; line-height:1.35; color:var(--plomb); overflow-wrap:anywhere; text-decoration:none; transition:color .2s; }
    .org-node .m:hover { color:var(--bleu); }
</style>

<section class="sec pub-team">
    <div class="sec-head">
        <div>
            <h1 class="sec-title">L'équipe</h1>
            <p class="pub-team-sub">L'organigramme de SIOKA.</p>
        </div>
    </div>

    <div class="org-board">
        <div class="org-scroll"><div class="org-tree" id="orgTree"></div></div>
    </div>
</section>

<script>
    // ── Organigramme public (lecture seule, données issues de la base) ──
    (function () {
        const page = document.querySelector('.pub-team');
        if (!page) return;

        const members = @json($members);
        const box = document.getElementById('orgTree');

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

        const kidsOf = (m) => members.filter(k => k.parent === m.id);

        function orgNode(m, depth) {
            const li = el('li');
            const node = el('div', 'org-node' + (depth === 0 ? ' lvl0' : ''));
            const av = el('div', 'team-avatar');
            setAvatar(av, m.photo, m.name);
            const txt = el('div', 'org-txt');
            txt.append(el('strong', '', m.name), el('span', 'r', m.role));
            if (m.email) {
                const mail = el('a', 'm', m.email);
                mail.href = 'mailto:' + m.email;
                txt.append(mail);
            }
            node.append(av, txt);
            li.append(node);

            const kids = kidsOf(m);
            if (kids.length) {
                const ul = el('ul');
                kids.forEach(k => ul.append(orgNode(k, depth + 1)));
                li.append(ul);
            }
            return li;
        }

        // Réduit l'organigramme pour qu'il tienne dans la largeur disponible (défilement sinon)
        function fitOrg() {
            const wrap = box.parentElement;
            box.style.zoom = 1;
            const tree = box.firstElementChild;
            if (!tree || !wrap.clientWidth) return;
            const need = tree.offsetWidth, avail = wrap.clientWidth - 8;
            box.style.zoom = need > avail ? Math.max(0.8, avail / need) : 1;
        }

        function renderOrg() {
            box.innerHTML = '';
            const ids = new Set(members.map(m => m.id));
            // Sommet : sans supérieur, ou dont le supérieur n'est pas affiché dans l'organigramme
            const roots = members.filter(m => m.parent == null || !ids.has(m.parent));
            if (!roots.length) { box.append(el('p', 'pub-team-empty', 'Aucun membre à afficher pour le moment.')); return; }
            const ul = el('ul');
            roots.forEach(r => ul.append(orgNode(r, 0)));
            box.append(ul);
            fitOrg();
        }

        // Un seul écouteur de redimensionnement, même après plusieurs navigations Turbo
        if (window.__equipeResize) window.removeEventListener('resize', window.__equipeResize);
        window.__equipeResize = fitOrg;
        window.addEventListener('resize', fitOrg);

        renderOrg();
    })();
</script>

@endsection