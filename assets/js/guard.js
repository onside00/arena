(() => {
  'use strict';
  // UI deterrence only. Never redirect a visitor from this script.
  // Device/country access decisions are made server-side in security.php.
  document.addEventListener('contextmenu', (e) => e.preventDefault(), true);
  document.addEventListener('copy', (e) => e.preventDefault(), true);
  document.addEventListener('cut', (e) => e.preventDefault(), true);
  document.addEventListener('dragstart', (e) => e.preventDefault(), true);
})();
