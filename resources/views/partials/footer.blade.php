{{-- ==========================================================
     PARTENAIRES DU PROJET — 3 niveaux :
     1. Initié par (ROHY) — badge agrandi (--featured)
     2. Mis en œuvre par (8 orgs, taille standard)
     3. Financé par (UE) — badge agrandi (--featured)
     Tous les logos sont des liens au comportement identique
     (nouvel onglet, rel sécurisé). 6 logos sont des PLACEHOLDERS,
     à remplacer dans public/images/partners/ dès réception des
     vrais fichiers.
     ========================================================== --}}
<section id="partners" class="partners" iaria-labelledby="partners-heading">
    <div class="partners-inner">

        {{-- Niveau 1 : initiateur du projet — mis en évidence --}}
        <div class="partners-tier">
            <p class="partners-heading">Projet initié par</p>
            <div class="partners-grid">
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="ROHY (nouvel onglet)">
                    <span class="partner-card-logo partner-card-logo--featured">
                        <img src="{{ asset('images/partners/logo-rohy.jpg') }}"
                             alt="ROHY" loading="lazy" width="320" height="140">
                    </span>
                </a>
            </div>
        </div>

        <div class="partners-divider" role="separator" aria-hidden="true"></div>

        {{-- Niveau 2 : tous les porteurs de mise en œuvre — taille standard --}}
        <div class="partners-tier partners-tier--network">
            <p class="partners-heading">Mis en œuvre par</p>
            <div class="partners-grid partners-grid--network">
                <a href="https://internews.org" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="Internews (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/internews.jpg') }}"
                             alt="Internews — Local voices. Global change."
                             loading="lazy" width="200" height="140">
                    </span>
                </a>
                <a href="https://manehoa.mg" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="#MANEHOA — Liberté d'expression Madagascar (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logo-manehoa.png') }}"
                             alt="#MANEHOA — Liberté d'expression Madagascar"
                             loading="lazy" width="277" height="117">
                    </span>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="MSIS Tatao (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logo-msis-Tatao.png') }}"
                             alt="MSIS Tatao" loading="lazy" width="320" height="140">
                    </span>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="Alliance Voahary Gasy (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logo-avg.png') }}"
                             alt="Alliance Voahary Gasy" loading="lazy" width="320" height="140">
                    </span>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="PFNOSCM (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logo-pfnoscm.jpg') }}"
                             alt="PFNOSCM" loading="lazy" width="320" height="140">
                    </span>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="ONG Ravintsara (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logo-ong-ravintsara.jpg') }}"
                             alt="ONG Ravintsara" loading="lazy" width="320" height="140">
                    </span>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="CCOC (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logo-ccoc.png') }}"
                             alt="CCOC" loading="lazy" width="320" height="140">
                    </span>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="SIF (nouvel onglet)">
                    <span class="partner-card-logo">
                        <img src="{{ asset('images/partners/logotypeSIF.png') }}"
                             alt="SIF" loading="lazy" width="320" height="140">
                    </span>
                </a>
            </div>
        </div>

        <div class="partners-divider" role="separator" aria-hidden="true"></div>

        {{-- Niveau 3 : financeur — mis en évidence --}}
        <div class="partners-tier">
            <p class="partners-heading">Financé par</p>
            <div class="partners-grid">
                <a href="https://european-union.europa.eu" target="_blank" rel="noopener noreferrer"
                   class="partner-card" aria-label="Union européenne — Unis pour un meilleur avenir (nouvel onglet)">
                    <span class="partner-card-logo partner-card-logo--featured">
                        <img src="{{ asset('images/partners/Logo HD_UE-Mada-01.png') }}"
                             alt="Union européenne — Unis pour un meilleur avenir"
                             loading="lazy" width="229" height="74">
                    </span>
                </a>
            </div>
        </div>

    </div>
</section>

<footer>
    <div class="footer-main">

        {{-- Bloc identité --}}
        <div class="footer-brand">
            <div class="logo">
                <div class="logo-main"><span class="logo-s">S</span><span class="logo-ioka">IOKA</span></div>
            </div>
            <p>
                Serasera lombonan'ny Olompirenena ho Kalitaon'ny Antontambaovao.<br>
                Presse indépendante de la société civile, une nouvelle signature dans le paysage médiatique malagasy.
            </p>
            <ul class="socials" aria-label="Réseaux sociaux SIOKA">
                <li>
                    <a href="#" class="social-btn" aria-label="Suivre SIOKA sur Facebook" target="_blank" rel="noopener noreferrer">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-btn" aria-label="Suivre SIOKA sur Twitter" target="_blank" rel="noopener noreferrer">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                        </svg>
                    </a>
                </li>
                <li>
                    <a href="#" class="social-btn" aria-label="S'abonner à la chaîne YouTube SIOKA" target="_blank" rel="noopener noreferrer">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33 2.78 2.78 0 0 0 1.94 2c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.33 29 29 0 0 0-.46-5.33z"/>
                            <polygon fill="var(--ink)" points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Colonnes de navigation, chacune reliée à son titre via aria-labelledby --}}
        <nav class="footer-col" aria-labelledby="footer-heading-emissions">
            <h4 id="footer-heading-emissions">Émissions SIOKA</h4>
            <ul>
                <li><a href="#">Safidy</a></li>
                <li><a href="#">Ekonomia</a></li>
                <li><a href="#">Ny Vaovao</a></li>
                <li><a href="#">Tontolo Iainana</a></li>
                <li><a href="#">Aiza ny pro ?</a></li>
                <li><a href="#">Siokan'Ampela</a></li>
            </ul>
        </nav>

        <nav class="footer-col" aria-labelledby="footer-heading-plateforme">
            <h4 id="footer-heading-plateforme">Plateforme</h4>
            <ul>
                <li><a href="#">Live Web TV</a></li>
                <li><a href="#">Live Webradio</a></li>
                <li><a href="#">Grille des programmes</a></li>
                <li><a href="#">Podcasts</a></li>
                <li><a href="#">L'Équipe</a></li>
            </ul>
        </nav>

        <div class="footer-col" aria-labelledby="footer-heading-contact">
            <h4 id="footer-heading-contact">Contact & Mentions</h4>
            <ul>
                <li><a href="mailto:contact@sioka.org">contact@sioka.org</a></li>
                <li><a href="https://sioka.org">www.sioka.org</a></li>
                <li>
                    <address>
                        VB6 Rue Samuel Rahamefy (ex-CITE)<br>
                        Ambatonakanga, Antananarivo 101
                    </address>
                </li>
                <li><a href="#">Mentions Légales</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <span>Mis en œuvre par la société civile Malagasy. <strong>© 2026 SIOKA</strong> — Tous droits réservés.</span>
    </div>
</footer>