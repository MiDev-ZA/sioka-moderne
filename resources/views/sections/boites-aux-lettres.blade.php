<style>
  .mb-section {
    max-width: var(--max);
    margin: clamp(40px, 6vw, 70px) auto 0;
    padding: 0 var(--gap);
  }
  .mb-inner {
    display: grid; grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
    gap: 40px clamp(32px, 5vw, 64px); align-items: start;
  }

  /* ---------- Colonne éditoriale ---------- */
  .mb-kicker {
    margin: 0 0 12px; font-family: var(--font-main); font-size: 12px; font-weight: 600;
    letter-spacing: .08em; text-transform: uppercase; color: var(--plomb);
  }
  .mb-title {
    display: flex; align-items: center; gap: 12px; margin: 0 0 14px;
    font-family: var(--font-main); font-size: clamp(26px, 3.4vw, 36px);
    font-weight: 800; line-height: 1.15; color: var(--blanc);
  }
  .mb-title::before {
    content: ''; width: 6px; height: 28px; flex-shrink: 0;
    background: var(--magenta); border-radius: 4px;
  }
  .mb-lead {
    margin: 0 0 32px; max-width: 46ch; font-family: var(--font-main);
    font-size: 15px; line-height: 1.65; color: var(--blanc-fort);
  }
  .mb-steps { margin: 0; padding: 0; list-style: none; }
  .mb-steps li {
    display: grid; grid-template-columns: 48px 1fr; gap: 2px 12px;
    padding: 16px 0; border-top: 1px solid var(--glass-bord);
  }
  .mb-steps li:last-child { border-bottom: 1px solid var(--glass-bord); }
  .mb-step-n {
    grid-row: span 2; font-family: var(--font-main); font-size: 26px;
    font-weight: 800; line-height: 1; color: var(--or);
  }
  .mb-step-t {
    margin: 0; font-family: var(--font-main); font-size: 14.5px;
    font-weight: 600; color: var(--blanc);
  }
  .mb-step-d {
    margin: 0; font-family: var(--font-main); font-size: 13.5px;
    font-weight: 300; line-height: 1.55; color: var(--plomb);
  }

  /* ---------- Panneau formulaire ---------- */
  .mb-panel {
    padding: 32px; background: var(--ink-2);
    border: 1px solid var(--glass-bord); border-top: 3px solid var(--magenta);
    border-radius: var(--r-xl);
  }
  .mb-form { display: grid; gap: 24px; }
  .mb-form[hidden] { display: none; }
  .mb-row { display: grid; grid-template-columns: 1fr 1fr; gap: 24px var(--gap); }
  .mb-field { display: grid; gap: 8px; min-width: 0; border: 0; padding: 0; margin: 0; }
  .mb-label {
    padding: 0; font-family: var(--font-main); font-size: 12px; font-weight: 600;
    letter-spacing: .06em; text-transform: uppercase; color: var(--plomb);
  }
  .mb-opt { text-transform: none; letter-spacing: 0; font-weight: 400; }

  .mb-input, .mb-textarea {
    width: 100%; box-sizing: border-box; padding: 14px 16px;
    font-family: var(--font-main); font-size: 15px; line-height: 1.5;
    color: var(--blanc); background: var(--ink-3);
    border: 1px solid var(--glass-bord); border-radius: var(--r-sm);
    outline: none; transition: border-color .25s var(--ease), box-shadow .25s var(--ease);
  }
  .mb-input::placeholder, .mb-textarea::placeholder { color: var(--plomb); }
  .mb-input:focus, .mb-textarea:focus {
    border-color: var(--bleu); box-shadow: 0 0 0 3px var(--bleu-glow);
  }
  .mb-textarea { min-height: 160px; resize: vertical; }
  .mb-field.is-invalid .mb-textarea { border-color: var(--magenta); }

  /* Sélecteur d'objet */
  .mb-chips { display: flex; flex-wrap: wrap; gap: 8px; }
  .mb-chip-input {
    position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0;
    overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;
  }
  .mb-chip {
    display: inline-block; padding: 8px 16px; font-family: var(--font-main);
    font-size: 13px; font-weight: 600; color: var(--blanc-fort);
    background: var(--blanc-doux); border: 1px solid var(--glass-bord);
    border-radius: 999px; cursor: pointer; user-select: none;
    transition: background .2s var(--ease), border-color .2s var(--ease), color .2s var(--ease);
  }
  .mb-chip:hover { background: var(--blanc-mid); }
  .mb-chip-input:checked + .mb-chip {
    background: var(--magenta); border-color: var(--magenta); color: var(--fixe-clair);
  }
  .mb-chip-input:focus-visible + .mb-chip { outline: 2px solid var(--bleu); outline-offset: 2px; }

  .mb-foot {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
  }
  .mb-meta { display: grid; gap: 4px; }
  .mb-count {
    font-family: var(--font-main); font-size: 12px; color: var(--plomb);
    font-variant-numeric: tabular-nums;
  }
  .mb-note { margin: 0; font-family: var(--font-main); font-size: 12.5px; color: var(--plomb); }

  .mb-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 10px;
    padding: 14px 28px; font-family: var(--font-main); font-size: 14px; font-weight: 700;
    letter-spacing: .5px; text-transform: uppercase; white-space: nowrap; cursor: pointer;
    color: var(--fixe-clair); background: var(--magenta);
    border: 1px solid var(--magenta); border-radius: var(--r-sm);
    transition: filter .2s var(--ease), transform .2s var(--ease);
  }
  .mb-btn:hover { filter: brightness(1.1); transform: translateY(-2px); }
  .mb-btn:focus-visible { outline: 2px solid var(--bleu); outline-offset: 2px; }
  .mb-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
  .mb-btn--ghost { color: var(--blanc); background: transparent; border-color: var(--plomb); }

  /* Erreur : texte lisible dans les deux thèmes, la couleur ne porte pas seule le sens */
  .mb-msg {
    margin: 0; padding: 4px 0 4px 12px; border-left: 3px solid var(--magenta);
    font-family: var(--font-main); font-size: 14px; font-weight: 500; color: var(--blanc-fort);
  }
  .mb-msg[hidden] { display: none; }

  /* ---------- Confirmation ---------- */
  .mb-done { display: grid; gap: 14px; justify-items: start; padding: 16px 0; outline: none; }
  .mb-done[hidden] { display: none; }
  .mb-done-icon {
    display: grid; place-items: center; width: 48px; height: 48px;
    border-radius: 50%; background: var(--vert-glow); color: var(--vert);
  }
  .mb-done-k {
    margin: 0; font-family: var(--font-main); font-size: 12px; font-weight: 600;
    letter-spacing: .08em; text-transform: uppercase; color: var(--plomb);
  }
  .mb-done-t {
    margin: 0; font-family: var(--font-main); font-size: clamp(24px, 3vw, 30px);
    font-weight: 800; line-height: 1.2; color: var(--blanc);
  }
  .mb-done-p {
    margin: 0 0 8px; max-width: 46ch; font-family: var(--font-main);
    font-size: 15px; line-height: 1.6; color: var(--blanc-fort);
  }

  .mb-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }

  @media (max-width: 860px) {
    .mb-inner { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .mb-panel { padding: 24px 18px; }
    .mb-row { grid-template-columns: 1fr; }
    .mb-foot { flex-direction: column; align-items: stretch; }
  }
  @media (prefers-reduced-motion: reduce) {
    .mb-input, .mb-textarea, .mb-chip, .mb-btn { transition: none; }
    .mb-btn:hover { transform: none; }
  }
</style>

<section class="mb-section" id="boite-aux-lettres" aria-labelledby="mb-title">
  <div class="mb-inner">

    <div class="mb-intro">
      <p class="mb-kicker">Écrivez à la rédaction</p>
      <h3 class="mb-title" id="mb-title">Boîte aux lettres</h3>
      <p class="mb-lead">
        Une idée, un signalement, une question ou un témoignage : votre message est transmis
        directement à l'équipe de SIOKA.
      </p>

      <ol class="mb-steps">
        <li>
          <span class="mb-step-n">01</span>
          <p class="mb-step-t">Vous écrivez</p>
          <p class="mb-step-d">Choisissez un objet et rédigez votre message.</p>
        </li>
        <li>
          <span class="mb-step-n">02</span>
          <p class="mb-step-t">La rédaction lit</p>
          <p class="mb-step-d">Chaque message est lu par l'équipe éditoriale.</p>
        </li>
        <li>
          <span class="mb-step-n">03</span>
          <p class="mb-step-t">Suite donnée</p>
          <p class="mb-step-d">Réponse, relais à l'antenne ou piste de reportage.</p>
        </li>
      </ol>
    </div>

    <div class="mb-panel">

      <form class="mb-form" id="mb-form" method="POST"
            action="{{ route('mailbox.send') }}" novalidate>
        @csrf

        <fieldset class="mb-field">
          <legend class="mb-label">Objet</legend>
          <div class="mb-chips">
            @foreach (['Suggestion', 'Signalement', 'Question', 'Témoignage', 'Autre'] as $i => $objet)
              <div>
                <input type="radio" class="mb-chip-input" name="subject"
                       id="mb-subject-{{ $i }}" value="{{ $objet }}" @checked($i === 0)>
                <label class="mb-chip" for="mb-subject-{{ $i }}">{{ $objet }}</label>
              </div>
            @endforeach
          </div>
        </fieldset>

        <div class="mb-field" id="mb-message-field">
          <label class="mb-label" for="mb-message">Votre message</label>
          <textarea id="mb-message" name="message" class="mb-textarea"
                    placeholder="Écrivez votre message ici" maxlength="2000"
                    required aria-describedby="mb-msg"></textarea>
        </div>

        <div class="mb-row">
          <div class="mb-field">
            <label class="mb-label" for="mb-name">Nom <span class="mb-opt">(facultatif)</span></label>
            <input type="text" id="mb-name" name="name" class="mb-input"
                   autocomplete="name" maxlength="120">
          </div>
          <div class="mb-field">
            <label class="mb-label" for="mb-contact">E-mail ou téléphone <span class="mb-opt">(facultatif)</span></label>
            <input type="text" id="mb-contact" name="contact" class="mb-input"
                   autocomplete="email" maxlength="120">
          </div>
        </div>

        {{-- Honeypot anti-bots --}}
        <input type="text" name="website" class="mb-hp" tabindex="-1"
               autocomplete="off" aria-hidden="true">

        {{-- Anti-spam : temps passé sur le formulaire (rempli par le script au moment de l'envoi) --}}
        <input type="hidden" name="elapsed" id="mb-elapsed" value="">

        <div class="mb-foot">
          <div class="mb-meta">
            <span class="mb-count" id="mb-count" aria-hidden="true">0 / 2000</span>
            <p class="mb-note">Laissez un contact si vous souhaitez une réponse.</p>
          </div>
          <button class="mb-btn" type="submit">
            <span class="mb-btn-label">Envoyer</span>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
              <path d="M2 8h11M9 3.5 13.5 8 9 12.5" stroke="currentColor" stroke-width="1.6"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <p id="mb-msg" class="mb-msg" role="status" aria-live="polite" hidden></p>
      </form>

      <div class="mb-done" id="mb-done" hidden tabindex="-1">
        <span class="mb-done-icon" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M5 12.5 10 17.5 19 7" stroke="currentColor" stroke-width="2.2"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <p class="mb-done-k">Message envoyé</p>
        <p class="mb-done-t">Merci pour votre message</p>
        <p class="mb-done-p" id="mb-done-text">Il a bien été transmis à la rédaction de SIOKA.</p>
        <button class="mb-btn mb-btn--ghost" type="button" id="mb-again">
          <span class="mb-btn-label">Écrire un autre message</span>
        </button>
      </div>

    </div>

  </div>
</section>

<script>
(function () {
  const form = document.getElementById('mb-form');
  if (!form) return;

  const message = document.getElementById('mb-message');
  const field   = document.getElementById('mb-message-field');
  const count   = document.getElementById('mb-count');
  const msg     = document.getElementById('mb-msg');
  const done    = document.getElementById('mb-done');
  const doneTxt = document.getElementById('mb-done-text');
  const again   = document.getElementById('mb-again');
  const elapsed = document.getElementById('mb-elapsed');
  const btn     = form.querySelector('.mb-btn');
  const label   = btn.querySelector('.mb-btn-label');
  const idle    = label.textContent;

  const MIN = 10, MAX = 2000;

  // Début de saisie (horloge monotone : insensible à l'heure du téléphone)
  let startedAt = performance.now();

  function showError(text) {
    msg.textContent = text;                       // textContent : aucune injection HTML possible
    msg.hidden = false;
    field.classList.add('is-invalid');
    message.setAttribute('aria-invalid', 'true');
  }

  function clearError() {
    msg.hidden = true;
    field.classList.remove('is-invalid');
    message.removeAttribute('aria-invalid');
  }

  function loading(on) {
    btn.disabled = on;
    label.textContent = on ? 'Envoi…' : idle;
  }

  message.addEventListener('input', function () {
    count.textContent = message.value.length + ' / ' + MAX;
    clearError();
  });

  again.addEventListener('click', function () {
    done.hidden = true;
    form.hidden = false;
    startedAt = performance.now();
    message.focus();
  });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (btn.disabled) return;                     // évite le double envoi
    clearError();

    const text = message.value.trim();
    if (!text) { showError('Veuillez écrire votre message.'); message.focus(); return; }
    if (text.length < MIN) { showError('Votre message est trop court.'); message.focus(); return; }
    if (text.length > MAX) { showError('Votre message est trop long.'); message.focus(); return; }
    message.value = text;

    // Temps passé sur le formulaire, vérifié côté serveur (anti-bot)
    elapsed.value = Math.round(performance.now() - startedAt);

    loading(true);

    const ctrl  = new AbortController();
    const timer = setTimeout(function () { ctrl.abort(); }, 12000);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),                 // contient déjà le jeton CSRF (@csrf)
        signal: ctrl.signal
      });

      const data = await res.json().catch(function () { return {}; });

      if (res.ok) {
        if (data.message) doneTxt.textContent = data.message;
        form.reset();
        count.textContent = '0 / ' + MAX;
        form.hidden = true;
        done.hidden = false;
        done.focus();
      } else if (res.status === 422) {
        const errs = data.errors || {};
        const first = Object.keys(errs)[0];
        showError((first && errs[first][0]) || 'Message invalide.');
      } else if (res.status === 429) {
        showError(data.daily ? data.message : 'Trop de tentatives. Réessayez dans une minute.');
      } else if (res.status === 419) {
        showError('Session expirée, rechargez la page puis réessayez.');
      } else {
        showError('Une erreur est survenue. Réessayez plus tard.');
      }
    } catch (err) {
      showError(err.name === 'AbortError'
        ? 'Le serveur met trop de temps à répondre. Réessayez.'
        : 'Connexion impossible. Vérifiez votre réseau.');
    } finally {
      clearTimeout(timer);
      loading(false);
    }
  });
})();
</script>