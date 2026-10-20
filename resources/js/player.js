const audio = document.getElementById('audioStream');
const playBtn = document.getElementById('playBtn');
const iconPlay = document.getElementById('iconPlay');
const iconPause = document.getElementById('iconPause');
const rpTitle = document.getElementById('rpTitle');

if (audio && playBtn) {
  let isPlaying = false;

  const pauseRadio = () => {
    audio.pause();
    iconPlay.style.display = 'block';
    iconPause.style.display = 'none';
    playBtn.classList.remove('playing');
    rpTitle.textContent = 'Webradio en pause';
    isPlaying = false;
  };

  window.togglePlay = function () {
    if (isPlaying) {
      pauseRadio();
    } else {
      // Un podcast est peut-être en cours de lecture : on lui demande de se mettre en pause
      document.dispatchEvent(new CustomEvent('sioka:radio-play'));

      audio.play().catch(e => {
        console.log('Lecture auto bloquée ou erreur réseau', e);
        rpTitle.textContent = 'Erreur de connexion au flux';
      });
      iconPlay.style.display = 'none';
      iconPause.style.display = 'block';
      playBtn.classList.add('playing');
      rpTitle.textContent = 'SIOKA - FM 106 Mhz';
      isPlaying = true;
    }
  };

  playBtn.addEventListener('click', togglePlay);

  // Un podcast vient de démarrer : on met la webradio en pause si elle joue
  document.addEventListener('sioka:podcast-play', () => {
    if (isPlaying) pauseRadio();
  });
}