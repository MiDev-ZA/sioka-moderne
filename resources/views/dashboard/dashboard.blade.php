<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de bord — SIOKA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
  :root {
    --ink: #0A0A0F; --ink-2: #14141C; --ink-3: #1C1C28; --ink-4: #262635;
    --magenta: #C21F93; --magenta-glow: rgba(194,31,147,0.22);
    --bleu: #0091E0; --bleu-glow: rgba(0,145,224,0.20);
    --or: #D4AC2E; --vert: #2FBF71;
    --blanc: #F5F5F8; --blanc-fort: rgba(245,245,248,0.94);
    --blanc-mid: rgba(245,245,248,0.14); --blanc-doux: rgba(245,245,248,0.06);
    --plomb: #7C8BA0;
    --glass: rgba(10,10,15,0.66); --glass-bord: rgba(255,255,255,0.08);
    --blur: blur(18px);
    --font-main: 'Poppins', sans-serif;
    --font-display: 'DM Serif Display', serif;
    --r-sm: 10px; --r: 14px; --r-lg: 20px; --r-xl: 28px;
    --ease: cubic-bezier(.4,0,.2,1);
    --gap: clamp(12px,2.5vw,24px); --max: 1320px;
    color-scheme: dark;
  }
  :root[data-theme="light"] {
    --ink: #F6F6FA; --ink-2: #FFFFFF; --ink-3: #EEEFF4; --ink-4: #E1E3EC;
    --magenta-glow: rgba(194,31,147,0.10); --bleu-glow: rgba(0,145,224,0.10);
    --blanc: #14141C; --blanc-fort: rgba(20,20,28,0.92);
    --blanc-mid: rgba(20,20,28,0.07); --blanc-doux: rgba(20,20,28,0.04);
    --plomb: #626E82;
    --glass: rgba(255,255,255,0.72); --glass-bord: rgba(15,15,25,0.08);
    color-scheme: light;
  }
  *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
  body{
    font-family:var(--font-main); background:var(--ink); color:var(--blanc);
    min-height:100vh; -webkit-font-smoothing:antialiased;
    transition:background-color .35s var(--ease), color .35s var(--ease);
  }
  a{color:inherit; text-decoration:none;}
  button{cursor:pointer; font-family:var(--font-main); border:none; background:none;}
  img{display:block; max-width:100%;}

  /* Ambient orbs (discrets) */
  .ambient{ position:fixed; inset:0; z-index:0; overflow:hidden; pointer-events:none; }
  .orb{ position:absolute; border-radius:50%; filter:blur(110px); opacity:.28; }
  .orb-a{ width:600px; height:600px; background:var(--magenta); top:-18%; left:-10%; }
  .orb-b{ width:520px; height:520px; background:var(--bleu); bottom:-20%; right:-8%; }

  /* ── Topbar ─────────────────────────────────────────── */
  .topbar{
    position:sticky; top:0; z-index:100; background:var(--glass);
    backdrop-filter:var(--blur); -webkit-backdrop-filter:var(--blur);
    border-bottom:1px solid var(--glass-bord);
  }
  .topbar-row{ max-width:var(--max); margin:0 auto; padding:0 var(--gap); height:68px; display:flex; align-items:center; gap:20px; }
  .brand{ font-weight:900; font-size:20px; letter-spacing:-.5px; flex-shrink:0; }
  .brand em{ font-style:normal; color:var(--magenta); }
  .search{ flex:1; max-width:360px; position:relative; }
  .search svg{ position:absolute; left:13px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:var(--plomb); }
  .search input{
    width:100%; background:var(--blanc-doux); border:1px solid var(--glass-bord); border-radius:999px;
    padding:9px 14px 9px 38px; font-size:12.5px; color:var(--blanc); outline:none; transition:border-color .2s;
    font-family:var(--font-main);
  }
  .search input:focus{ border-color:var(--bleu); }
  .search input::placeholder{ color:var(--plomb); }
  .topbar-spacer{ flex:1; }
  .icon-btn{
    width:38px; height:38px; border-radius:50%; position:relative;
    display:flex; align-items:center; justify-content:center;
    background:var(--blanc-doux); border:1px solid var(--glass-bord); color:var(--plomb); transition:all .2s;
  }
  .icon-btn:hover{ color:var(--blanc); background:var(--blanc-mid); }
  .icon-btn svg{ width:16px; height:16px; }
  .icon-btn .ping{ position:absolute; top:7px; right:8px; width:7px; height:7px; border-radius:50%; background:var(--magenta); box-shadow:0 0 0 2px var(--ink-2); }
  .topbar-actions{ display:flex; align-items:center; gap:8px; }
  .avatar-chip{
    width:38px; height:38px; border-radius:50%; padding:2px;
    background:linear-gradient(135deg,var(--magenta),var(--bleu));
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
  }
  .avatar-chip span{
    width:100%; height:100%; border-radius:50%; background:var(--ink-2); color:var(--blanc);
    display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;
  }

  /* ── Shell ──────────────────────────────────────────── */
  .shell{ position:relative; z-index:1; max-width:var(--max); margin:clamp(20px,3vw,30px) auto 60px; padding:0 var(--gap); display:grid; grid-template-columns:250px 1fr; gap:var(--gap); align-items:start; }

  /* Sidebar */
  .side{ position:sticky; top:88px; display:flex; flex-direction:column; gap:14px; }
  .side-card{ background:var(--ink-2); border:1px solid var(--glass-bord); border-radius:var(--r-xl); overflow:hidden; }
  .side-user{ display:flex; flex-direction:column; align-items:center; text-align:center; gap:10px; padding:26px 18px; background:linear-gradient(160deg, var(--magenta-glow), transparent 70%); }
  .side-avatar{ width:60px; height:60px; border-radius:50%; padding:3px; background:linear-gradient(135deg,var(--magenta),var(--bleu)); }
  .side-avatar span{ width:100%; height:100%; border-radius:50%; background:var(--ink-3); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; color:var(--blanc); }
  .side-user h3{ font-size:14px; font-weight:700; color:var(--blanc); }
  .side-user p{ font-size:11px; color:var(--plomb); text-transform:uppercase; letter-spacing:.6px; }

  .nav{ display:flex; flex-direction:column; padding:8px; gap:2px; position:relative; }
  .nav-label{ font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:var(--plomb); padding:12px 12px 6px; }
  .nav a{
    display:flex; align-items:center; gap:12px; font-size:13px; font-weight:600; color:var(--plomb);
    padding:10px 12px; border-radius:var(--r-sm); transition:background .2s, color .2s; position:relative;
  }
  .nav a svg{ width:16px; height:16px; flex-shrink:0; }
  .nav a:hover{ color:var(--blanc); background:var(--blanc-doux); }
  .nav a.active{ color:#fff; background:linear-gradient(135deg,var(--magenta),var(--bleu)); box-shadow:0 8px 18px -8px var(--magenta-glow); }
  .nav-sep{ height:1px; background:var(--glass-bord); margin:8px 12px; }

  .side-live{ padding:18px; }
  .side-live-top{ display:flex; align-items:center; gap:6px; margin-bottom:10px; }
  .side-live-top .dot{ width:6px; height:6px; border-radius:50%; background:var(--magenta); animation:blink 1.4s infinite; }
  @keyframes blink{ 0%,100%{opacity:1;} 50%{opacity:.25;} }
  .side-live-top span{ font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:var(--magenta); }
  .side-live p{ font-size:12px; color:var(--blanc-fort); font-weight:500; margin-bottom:12px; line-height:1.4; }
  .side-live-btn{ width:100%; display:flex; align-items:center; justify-content:center; gap:6px; background:var(--ink-3); border:1px solid var(--glass-bord); color:var(--blanc); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:10px; border-radius:var(--r-sm); transition:all .2s; }
  .side-live-btn:hover{ border-color:var(--magenta); color:var(--magenta); }
  .side-live-btn svg{ width:13px; height:13px; }

  /* Main */
  .main{ display:flex; flex-direction:column; gap:clamp(22px,3vw,30px); min-width:0; }

  .welcome{ display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; }
  .welcome h1{ font-family:var(--font-display); font-style:italic; font-weight:400; font-size:clamp(24px,2.8vw,32px); color:var(--blanc); }
  .welcome h1 em{ font-family:var(--font-main); font-style:normal; font-weight:800; color:var(--magenta); }
  .welcome p{ font-size:12.5px; color:var(--plomb); margin-top:4px; }
  .date-pill{ font-size:11.5px; font-weight:600; color:var(--plomb); background:var(--blanc-doux); border:1px solid var(--glass-bord); padding:8px 14px; border-radius:999px; white-space:nowrap; }

  /* Stats */
  .stats{ display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
  .stat{
    background:var(--ink-2); border:1px solid var(--glass-bord); border-radius:var(--r-lg);
    padding:20px; position:relative; overflow:hidden; transition:transform .2s, border-color .2s;
  }
  .stat:hover{ transform:translateY(-3px); border-color:var(--stat-color, var(--magenta)); }
  .stat-top{ display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .stat-icon{
    width:38px; height:38px; border-radius:var(--r-sm);
    background:var(--stat-glow, var(--magenta-glow)); color:var(--stat-color, var(--magenta));
    display:flex; align-items:center; justify-content:center;
  }
  .stat-icon svg{ width:18px; height:18px; }
  .stat-delta{ font-size:11px; font-weight:700; padding:3px 8px; border-radius:999px; }
  .stat-delta.up{ color:var(--vert); background:rgba(47,191,113,.12); }
  .stat-delta.down{ color:var(--magenta); background:var(--magenta-glow); }
  .stat-value{ font-size:26px; font-weight:800; color:var(--blanc); }
  .stat-label{ font-size:11.5px; color:var(--plomb); margin-top:2px; }
  .spark{ width:100%; height:28px; margin-top:12px; }

  /* Section headers */
  .sec-title{ font-size:14.5px; font-weight:800; color:var(--blanc); display:flex; align-items:center; gap:10px; }
  .sec-title::before{ content:''; width:5px; height:17px; background:linear-gradient(var(--magenta),var(--bleu)); border-radius:4px; }
  .sec-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
  .sec-link{ font-size:11.5px; font-weight:600; color:var(--bleu); }
  .sec-link:hover{ color:var(--blanc); }

  /* Quick actions */
  .actions{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
  .action{
    background:var(--ink-2); border:1px solid var(--glass-bord); border-radius:var(--r-lg);
    padding:20px; display:flex; flex-direction:column; gap:12px; transition:border-color .2s, transform .2s;
  }
  .action:hover{ border-color:var(--bleu); transform:translateY(-3px); }
  .action-top{ display:flex; align-items:center; justify-content:space-between; }
  .action-icon{ width:36px; height:36px; border-radius:var(--r-sm); background:var(--bleu-glow); color:var(--bleu); display:flex; align-items:center; justify-content:center; }
  .action-icon svg{ width:17px; height:17px; }
  .action-arrow{ color:var(--plomb); transition:transform .2s, color .2s; }
  .action:hover .action-arrow{ color:var(--blanc); transform:translateX(3px); }
  .action-arrow svg{ width:15px; height:15px; }
  .action h4{ font-size:13.5px; font-weight:700; color:var(--blanc); }
  .action p{ font-size:11.5px; color:var(--plomb); line-height:1.5; }

  /* Grid 2 : activité + live */
  .grid2{ display:grid; grid-template-columns:1.5fr 1fr; gap:var(--gap); align-items:start; }
  .panel{ background:var(--ink-2); border:1px solid var(--glass-bord); border-radius:var(--r-xl); overflow:hidden; }
  .panel-pad{ padding:18px 20px 6px; }

  .activity{ display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid var(--glass-bord); }
  .activity:last-child{ border-bottom:none; }
  .activity-icon{ width:34px; height:34px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:var(--a-glow); color:var(--a-color); }
  .activity-icon svg{ width:15px; height:15px; }
  .activity-text{ flex:1; min-width:0; }
  .activity-text p{ font-size:13px; color:var(--blanc-fort); font-weight:500; }
  .activity-text p strong{ color:var(--blanc); }
  .activity-text span{ font-size:11px; color:var(--plomb); }
  .pill{ font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:4px 10px; border-radius:999px; flex-shrink:0; }
  .pill.ok{ background:rgba(47,191,113,.14); color:var(--vert); }
  .pill.pending{ background:var(--or); color:#000; opacity:.85; }

  /* Live panel */
  .live-panel{ padding:22px; }
  .live-top{ display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
  .live-title{ font-size:11.5px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--blanc); }
  .live-badge{ display:flex; align-items:center; gap:5px; font-size:10px; font-weight:700; color:var(--magenta); text-transform:uppercase; letter-spacing:.5px; }
  .live-badge .dot{ width:6px; height:6px; border-radius:50%; background:var(--magenta); animation:blink 1.4s infinite; }

  .radial{ position:relative; width:150px; height:150px; margin:0 auto 18px; }
  .radial svg{ width:100%; height:100%; transform:rotate(-90deg); }
  .radial-center{ position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; }
  .radial-center strong{ font-size:24px; font-weight:800; color:var(--blanc); line-height:1; }
  .radial-center span{ font-size:10px; color:var(--plomb); text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }

  .trend-label{ display:flex; align-items:center; justify-content:space-between; font-size:11px; color:var(--plomb); margin-bottom:6px; }
  .trend{ width:100%; height:44px; margin-bottom:18px; }

  .mini-player{ display:flex; align-items:center; gap:12px; background:var(--ink-3); border:1px solid var(--glass-bord); border-radius:var(--r-sm); padding:10px 12px; }
  .mini-play{ width:34px; height:34px; border-radius:50%; flex-shrink:0; background:linear-gradient(135deg,var(--magenta),var(--bleu)); color:#fff; display:flex; align-items:center; justify-content:center; }
  .mini-play svg{ width:14px; height:14px; }
  .mini-text p{ font-size:12px; font-weight:600; color:var(--blanc); }
  .mini-text span{ font-size:10.5px; color:var(--plomb); }

  /* Top contenus */
  .rank-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; }
  .rank-card{ background:var(--ink-2); border:1px solid var(--glass-bord); border-radius:var(--r-lg); padding:16px; display:flex; align-items:center; gap:14px; }
  .rank-num{ font-family:var(--font-display); font-style:italic; font-size:26px; color:var(--plomb); flex-shrink:0; width:30px; }
  .rank-card:nth-child(1) .rank-num{ color:var(--or); }
  .rank-info p{ font-size:12.5px; font-weight:700; color:var(--blanc); line-height:1.35; margin-bottom:3px; }
  .rank-info span{ font-size:11px; color:var(--plomb); }

  @media (prefers-reduced-motion:reduce){ .side-live-top .dot, .live-badge .dot{ animation:none; } }
  @media (max-width:1100px){ .stats{ grid-template-columns:repeat(2,1fr); } .actions{ grid-template-columns:1fr; } .grid2{ grid-template-columns:1fr; } .rank-grid{ grid-template-columns:1fr; } }
  @media (max-width:860px){ .shell{ grid-template-columns:1fr; } .side{ position:static; flex-direction:row; overflow-x:auto; } .side-card{ flex-shrink:0; min-width:260px; } }
  @media (max-width:560px){ .stats{ grid-template-columns:1fr; } .search{ display:none; } }
</style>
</head>
<body>

  <div class="ambient"><div class="orb orb-a"></div><div class="orb orb-b"></div></div>

  <header class="topbar">
    <div class="topbar-row">
      <div class="brand">S<em>IOKA</em></div>
      <div class="search">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" placeholder="Rechercher un contenu…">
      </div>
      <div class="topbar-spacer"></div>
      <div class="topbar-actions">
        <button class="icon-btn" id="themeToggle" title="Changer de thème">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
        </button>
        <button class="icon-btn" title="Notifications">
          <span class="ping"></span>
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </button>
        <div class="avatar-chip"><span>R</span></div>
      </div>
    </div>
  </header>

  <div class="shell">

    <aside class="side">
      <div class="side-card">
        <div class="side-user">
          <div class="side-avatar"><span>R</span></div>
          <div>
            <h3>Rindra Andria</h3>
            <p>Journaliste</p>
          </div>
        </div>

        <nav class="nav">
          <div class="nav-label">Général</div>
          <a href="#" class="active">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            Vue d'ensemble
          </a>
          <a href="#">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M14 4v5h5"/></svg>
            Actualités
          </a>
          <a href="#">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 2"/></svg>
            Podcasts
          </a>
          <a href="#">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            RushServer
          </a>
          <div class="nav-sep"></div>
          <div class="nav-label">Compte</div>
          <a href="#">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/></svg>
            Mon profil
          </a>
          <a href="#">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Déconnexion
          </a>
        </nav>
      </div>

      <div class="side-card side-live">
        <div class="side-live-top"><span class="dot"></span><span>À l'antenne</span></div>
        <p>SIOKA Webradio — FM 106 Mhz</p>
        <button class="side-live-btn">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Écouter le direct
        </button>
      </div>
    </aside>

    <main class="main">

      <div class="welcome">
        <div>
          <h1>Bonjour, <em>Rindra</em> <span style="font-family:var(--font-main);">👋</span></h1>
          <p>Voici un aperçu de l'activité SIOKA aujourd'hui.</p>
        </div>
        <div class="date-pill">Jeudi 2 juillet 2026</div>
      </div>

      <div class="stats">
        <div class="stat" style="--stat-glow:var(--magenta-glow); --stat-color:var(--magenta);">
          <div class="stat-top">
            <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/></svg></div>
            <span class="stat-delta up">▲ 8,2%</span>
          </div>
          <div class="stat-value">1 284</div>
          <div class="stat-label">Auditeurs live</div>
          <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
            <polyline points="0,20 14,17 28,19 42,10 56,13 70,6 84,9 100,3" fill="none" stroke="var(--magenta)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <div class="stat" style="--stat-glow:var(--bleu-glow); --stat-color:var(--bleu);">
          <div class="stat-top">
            <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="M14 4v5h5"/></svg></div>
            <span class="stat-delta up">▲ 5</span>
          </div>
          <div class="stat-value">37</div>
          <div class="stat-label">Actualités publiées</div>
          <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
            <polyline points="0,22 14,20 28,15 42,17 56,9 70,12 84,7 100,8" fill="none" stroke="var(--bleu)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <div class="stat" style="--stat-glow:rgba(212,172,46,.18); --stat-color:var(--or);">
          <div class="stat-top">
            <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 2"/></svg></div>
            <span class="stat-delta up">▲ 2</span>
          </div>
          <div class="stat-value">18</div>
          <div class="stat-label">Podcasts en ligne</div>
          <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
            <polyline points="0,18 14,19 28,14 42,16 56,11 70,13 84,8 100,10" fill="none" stroke="var(--or)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>

        <div class="stat" style="--stat-glow:rgba(124,139,160,.18); --stat-color:var(--plomb);">
          <div class="stat-top">
            <div class="stat-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <span class="stat-delta down">▼ 2</span>
          </div>
          <div class="stat-value">6</div>
          <div class="stat-label">Rushes en attente</div>
          <svg class="spark" viewBox="0 0 100 28" preserveAspectRatio="none">
            <polyline points="0,8 14,10 28,9 42,14 56,13 70,18 84,17 100,22" fill="none" stroke="var(--plomb)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>

      <section>
        <div class="sec-head"><div class="sec-title">Actions rapides</div></div>
        <div class="actions">
          <a href="#" class="action">
            <div class="action-top">
              <div class="action-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div>
              <span class="action-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
            <h4>Publier une actualité</h4>
            <p>Rédigez et publiez un nouvel article sur le site SIOKA.</p>
          </a>
          <a href="#" class="action">
            <div class="action-top">
              <div class="action-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
              <span class="action-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
            <h4>Envoyer un rush</h4>
            <p>Déposez un fichier audio ou vidéo vers RushServer.</p>
          </a>
          <a href="#" class="action">
            <div class="action-top">
              <div class="action-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 2"/></svg></div>
              <span class="action-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </div>
            <h4>Gérer les podcasts</h4>
            <p>Ajoutez ou modifiez un épisode de podcast.</p>
          </a>
        </div>
      </section>

      <div class="grid2">
        <section class="panel">
          <div class="panel-pad">
            <div class="sec-head">
              <div class="sec-title">Activité récente</div>
              <a href="#" class="sec-link">Tout voir</a>
            </div>
          </div>

          <div class="activity" style="--a-glow:var(--bleu-glow); --a-color:var(--bleu);">
            <div class="activity-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/></svg></div>
            <div class="activity-text">
              <p><strong>Élections communales</strong> — article mis à jour</p>
              <span>Il y a 24 minutes</span>
            </div>
            <span class="pill ok">Publié</span>
          </div>

          <div class="activity" style="--a-glow:var(--magenta-glow); --a-color:var(--magenta);">
            <div class="activity-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <div class="activity-text">
              <p><strong>Interview_Marché_Analakely.mp4</strong> déposé</p>
              <span>Il y a 2 heures</span>
            </div>
            <span class="pill pending">À monter</span>
          </div>

          <div class="activity" style="--a-glow:rgba(212,172,46,.16); --a-color:var(--or);">
            <div class="activity-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 2"/></svg></div>
            <div class="activity-text">
              <p><strong>Épisode 42 — Culture Malagasy</strong> publié</p>
              <span>Hier à 18:40</span>
            </div>
            <span class="pill ok">En ligne</span>
          </div>

          <div class="activity" style="--a-glow:var(--bleu-glow); --a-color:var(--bleu);">
            <div class="activity-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/></svg></div>
            <div class="activity-text">
              <p><strong>Budget municipal 2027</strong> — brouillon enregistré</p>
              <span>Hier à 15:02</span>
            </div>
            <span class="pill pending">Brouillon</span>
          </div>
        </section>

        <section class="panel live-panel">
          <div class="live-top">
            <span class="live-title">SIOKA en direct</span>
            <span class="live-badge"><span class="dot"></span>Live</span>
          </div>

          <div class="radial">
            <svg viewBox="0 0 150 150">
              <circle cx="75" cy="75" r="64" fill="none" stroke="var(--glass-bord)" stroke-width="10"/>
              <circle cx="75" cy="75" r="64" fill="none" stroke="url(#g1)" stroke-width="10" stroke-linecap="round" stroke-dasharray="402" stroke-dashoffset="90"/>
              <defs>
                <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0%" stop-color="var(--magenta)"/>
                  <stop offset="100%" stop-color="var(--bleu)"/>
                </linearGradient>
              </defs>
            </svg>
            <div class="radial-center">
              <strong>1 284</strong>
              <span>Auditeurs</span>
            </div>
          </div>

          <div class="trend-label"><span>Tendance 7 jours</span><span>+12%</span></div>
          <svg class="trend" viewBox="0 0 200 44" preserveAspectRatio="none">
            <defs>
              <linearGradient id="g2" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="var(--bleu)" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="var(--bleu)" stop-opacity="0"/>
              </linearGradient>
            </defs>
            <path d="M0,34 L28,28 L57,31 L85,18 L114,22 L142,10 L171,15 L200,4 L200,44 L0,44 Z" fill="url(#g2)"/>
            <polyline points="0,34 28,28 57,31 85,18 114,22 142,10 171,15 200,4" fill="none" stroke="var(--bleu)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>

          <div class="mini-player">
            <button class="mini-play" aria-label="Écouter"><svg fill="currentColor" viewBox="0 0 24 24"><polygon points="6 4 20 12 6 20 6 4"/></svg></button>
            <div class="mini-text">
              <p>SIOKA Webradio</p>
              <span>FM 106 Mhz · En direct</span>
            </div>
          </div>
        </section>
      </div>

      <section>
        <div class="sec-head"><div class="sec-title">Top contenus de la semaine</div><a href="#" class="sec-link">Statistiques</a></div>
        <div class="rank-grid">
          <div class="rank-card">
            <span class="rank-num">01</span>
            <div class="rank-info"><p>Élections communales : les résultats commune par commune</p><span>4 210 lectures</span></div>
          </div>
          <div class="rank-card">
            <span class="rank-num">02</span>
            <div class="rank-info"><p>Épisode 42 — Culture Malagasy</p><span>1 860 écoutes</span></div>
          </div>
          <div class="rank-card">
            <span class="rank-num">03</span>
            <div class="rank-info"><p>Marché d'Analakely : reportage terrain</p><span>1 402 lectures</span></div>
          </div>
        </div>
      </section>

    </main>
  </div>

  <script>
    document.getElementById('themeToggle').addEventListener('click', () => {
      const root = document.documentElement;
      if (root.getAttribute('data-theme') === 'light') root.removeAttribute('data-theme');
      else root.setAttribute('data-theme', 'light');
    });
  </script>
</body>
</html>