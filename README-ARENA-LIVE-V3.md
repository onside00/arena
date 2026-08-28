# Arena Live v3

This package is based on the current `onside00/arena` PHP/MySQL project structure.

Changes:
- Public header rebuilt with RTL-friendly Flexbox.
- Telegram button links to https://t.me/onside_plus and displays "الرئيسية".
- Center logo uses `assets/img/logo.png`, circular 65x65.
- Site title changed to "أرينا لايف".
- Public match cards now show league, status, time, team logos/names, and a مشاهدة المباراة button.
- Cards are larger and more prominent.
- Existing admin panel, ordering, uploads, security, maintenance, database schema, and redirects are preserved.

Deployment:
1. Upload the package contents to the repository root.
2. On the VPS:
   cd /var/www/sports-live
   sudo git fetch origin
   sudo git reset --hard origin/main
   sudo apachectl configtest
   sudo systemctl reload apache2

No database re-import is required.
