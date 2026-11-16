<style>
  .nl-section {
    --nl-accent: var(--accent, #2563eb);  /* reprend votre variable --accent si elle existe */
    padding: 56px 16px;
    border-top: 1px solid color-mix(in srgb, currentColor 14%, transparent);
  }
  .nl-inner {
    max-width: 1040px; margin: 0 auto;
    display: flex; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 32px 48px;
  }
  .nl-text { flex: 1 1 340px; }
  .nl-text h3 { margin: 0 0 8px; font-size: 24px; font-weight: 700; line-height: 1.3; color: inherit; }
  .nl-text p  { margin: 0; font-size: 15px; line-height: 1.6; opacity: .72; }

  .nl-form-wrap { flex: 1 1 380px; max-width: 460px; }
  .nl-form {
    display: flex; overflow: hidden; border-radius: 10px;
    border: 1px solid color-mix(in srgb, currentColor 22%, transparent);
    background: color-mix(in srgb, currentColor 4%, transparent);
    transition: border-color .15s, box-shadow .15s;
  }
  .nl-form:focus-within {
    border-color: var(--nl-accent);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--nl-accent) 25%, transparent);
  }
  .nl-form.is-invalid { border-color: #ef4444; }
  .nl-input {
    flex: 1; min-width: 0; padding: 14px 16px; font-size: 15px;
    color: inherit; background: transparent; border: 0; outline: none;
  }
  .nl-input::placeholder { color: inherit; opacity: .5; }

  .btn-submit {
    padding: 0 24px; font-size: 15px; font-weight: 600; color: #fff;
    background: var(--nl-accent); border: 0; cursor: pointer; white-space: nowrap;
    transition: filter .15s;
  }
  .btn-submit:hover { filter: brightness(1.1); }
  .btn-submit:disabled { opacity: .65; cursor: not-allowed; }

  .nl-msg { margin: 10px 0 0; font-size: 14px; font-weight: 500; }
  .nl-msg[hidden] { display: none; }
  .nl-ok { color: #22c55e; }
  .nl-error { color: #ef4444; }
  .nl-note { margin: 10px 0 0; font-size: 12.5px; opacity: .6; }

  .nl-section .visually-hidden {
    position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0;
    overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
  }
  .nl-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }

  @media (max-width: 640px) {
    .nl-form { flex-direction: column; }
    .btn-submit { padding: 14px; }
  }
</style>

<section class="nl-section" id="contact" aria-labelledby="nl-title">
  <div class="nl-inner">

    <div class="nl-text">
      <h3 id="nl-title">Haino vaky jerin'ny Olompirenena</h3>
      <p>Inscrivez-vous pour recevoir les grilles de programme SIOKA, les résumés d'actualité et les alertes de diffusion directe.</p>
    </div>

    <div class="nl-form-wrap">
      <form class="nl-form" id="nl-form" method="POST"
            action="{{ route('newsletter.subscribe') }}" novalidate>
        @csrf
        <label for="nl-email" class="visually-hidden">Adresse e-mail</label>
        <input type="email" id="nl-email" name="email" class="nl-input"
               placeholder="votre@email.com" autocomplete="email"
               inputmode="email" maxlength="255" required aria-describedby="nl-msg">

        {{-- Honeypot anti-bots --}}
        <input type="text" name="website" class="nl-hp" tabindex="-1"
               autocomplete="off" aria-hidden="true">

        <button class="btn-submit" type="submit">S'abonner</button>
      </form>

      <p id="nl-msg" class="nl-msg" role="status" aria-live="polite" hidden></p>

    </div>

  </div>
</section>

<script>
(function () {
  const form = document.getElementById('nl-form');
  if (!form) return;

  const input = document.getElementById('nl-email');
  const msg   = document.getElementById('nl-msg');
  const btn   = form.querySelector('.btn-submit');
  const idle  = btn.textContent;

  // Même règle que le serveur : liste blanche de caractères
  const VALID = /^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/;

  function show(text, ok) {
    msg.textContent = text;                       // textContent : aucune injection HTML possible
    msg.className = 'nl-msg ' + (ok ? 'nl-ok' : 'nl-error');
    msg.hidden = false;
    form.classList.toggle('is-invalid', !ok);
  }

  function loading(on) {
    btn.disabled = on;
    btn.textContent = on ? 'Envoi…' : idle;
  }

  input.addEventListener('input', function () {
    form.classList.remove('is-invalid');
    msg.hidden = true;
  });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (btn.disabled) return;                     // évite le double envoi
    msg.hidden = true;

    const email = input.value.trim();
    if (!email) { show('Veuillez saisir votre adresse e-mail.', false); input.focus(); return; }
    if (email.length > 254 || !VALID.test(email)) {
      show('Adresse e-mail invalide.', false); input.focus(); return;
    }
    input.value = email;

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
        show(data.message || 'Merci pour votre inscription !', true);
        form.reset();
      } else if (res.status === 422) {
        show((data.errors && data.errors.email && data.errors.email[0]) || 'Adresse e-mail invalide.', false);
      } else if (res.status === 429) {
        show('Trop de tentatives. Réessayez dans une minute.', false);
      } else if (res.status === 419) {
        show('Session expirée, rechargez la page puis réessayez.', false);
      } else {
        show('Une erreur est survenue. Réessayez plus tard.', false);
      }
    } catch (err) {
      show(err.name === 'AbortError'
        ? 'Le serveur met trop de temps à répondre. Réessayez.'
        : 'Connexion impossible. Vérifiez votre réseau.', false);
    } finally {
      clearTimeout(timer);
      loading(false);
    }
  });
})();
</script>