(() => {
  'use strict';
  document.querySelectorAll('form.confirm-delete').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm('هل أنت متأكد من الحذف؟')) {
        event.preventDefault();
      }
    });
  });
})();
