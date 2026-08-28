(() => {
  'use strict';

  let locked = false;
  const lock = () => {
    if (locked) return;
    locked = true;
    try {
      document.documentElement.innerHTML = '';
      window.location.replace('/maintenance.php?m=1');
    } catch (_) {
      window.location.href = '/maintenance.php?m=1';
    }
  };

  const desktopPlatform = () => {
    let platform = '';
    try {
      platform = navigator.userAgentData?.platform || navigator.platform || '';
    } catch (_) {}
    if (/Win32|Win64|Windows|MacIntel|MacPPC|CrOS|Linux x86_64|Linux i686/i.test(platform)) {
      lock();
    }
  };

  const threshold = 180;
  let strikes = 0;
  const checkDevtools = () => {
    const widthGap = Math.abs(window.outerWidth - window.innerWidth);
    const heightGap = Math.abs(window.outerHeight - window.innerHeight);
    if (widthGap > threshold || heightGap > threshold) {
      strikes++;
      if (strikes >= 2) lock();
    } else {
      strikes = 0;
    }
  };

  desktopPlatform();
  checkDevtools();
  setInterval(desktopPlatform, 1000);
  setInterval(checkDevtools, 700);

  document.addEventListener('contextmenu', (e) => e.preventDefault(), true);
  document.addEventListener('copy', (e) => e.preventDefault(), true);
  document.addEventListener('cut', (e) => e.preventDefault(), true);
  document.addEventListener('selectstart', (e) => e.preventDefault(), true);
  document.addEventListener('dragstart', (e) => e.preventDefault(), true);

  document.addEventListener('keydown', (e) => {
    const key = (e.key || '').toLowerCase();
    const blocked =
      e.key === 'F12' ||
      (e.ctrlKey && e.shiftKey && ['i','j','c'].includes(key)) ||
      (e.ctrlKey && ['u','s'].includes(key)) ||
      (e.metaKey && e.altKey && ['i','j','c'].includes(key));

    if (blocked) {
      e.preventDefault();
      e.stopImmediatePropagation();
      lock();
    }
  }, true);
})();
