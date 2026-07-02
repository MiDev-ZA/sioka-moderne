const audio = document.getElementById('audioStream');
const playBtn = document.getElementById('playBtn');
const iconPlay = document.getElementById('iconPlay');
const iconPause = document.getElementById('iconPause');
const rpTitle = document.getElementById('rpTitle');
const volSlider = document.getElementById('volSlider');
const volFill = document.getElementById('volFill');

let isPlaying = false;

window.togglePlay = function() {
  if (isPlaying) {
    audio.pause();
    audio.src = ""; 
    iconPlay.style.display = 'block';
    iconPause.style.display = 'none';
    playBtn.classList.remove('playing');
    rpTitle.textContent = "Webradio en pause";
  } else {
    audio.src = "https://securestreams.autopo.st:2584/;"; 
    audio.play().catch(e => {
      console.log("Lecture auto bloquée ou erreur réseau", e);
      rpTitle.textContent = "Erreur de connexion au flux";
    });
    iconPlay.style.display = 'none';
    iconPause.style.display = 'block';
    playBtn.classList.add('playing');
    rpTitle.textContent = "SIOKA Webradio - FM 106 Mhz";
  }
  isPlaying = !isPlaying;
};

playBtn.addEventListener('click', togglePlay);

let isDraggingVol = false;

function setVolume(e) {
  const rect = volSlider.getBoundingClientRect();
  let x = e.clientX - rect.left;
  if (x < 0) x = 0;
  if (x > rect.width) x = rect.width;
  const pct = x / rect.width;
  volFill.style.width = (pct * 100) + '%';
  audio.volume = pct;
}

volSlider.addEventListener('mousedown', (e) => {
  isDraggingVol = true;
  setVolume(e);
});

document.addEventListener('mousemove', (e) => {
  if (isDraggingVol) setVolume(e);
});

document.addEventListener('mouseup', () => {
  isDraggingVol = false;
});