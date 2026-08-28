document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('img.team-logo').forEach((img) => {
    img.addEventListener('error', () => {
      const wrap = img.closest('.team-logo-wrap');
      if (wrap) wrap.style.opacity = '.35';
      img.style.display = 'none';
    }, { once: true });
  });
});
