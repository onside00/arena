# Arena 4K v2 update

## Included changes
- New professional mobile-first design.
- Arena 4K logo at the top, then site name, then match cards.
- Smaller rectangular match cards with only team logos/names and VS.
- Public view counters removed.
- Admin can move cards up/down; ordering is stored in `display_order`.
- Existing database auto-upgrades on first request; no manual SQL import is required for an existing site.
- Team logos accept PNG/JPG/JPEG/WebP/GIF/AVIF up to 5 MB.
- Desktop/laptop and blocked countries get a custom maintenance page.
- Maintenance page includes a **شاهد من الموقع** button to `https://www.arena8x.com`.
- No site-wide public IP ban is created by PHP.

## Important: Cloudflare Worker
The currently deployed Worker that stores 24-hour IP bans must also be replaced with the no-ban Worker supplied separately in `arena-edge-no-ip-ban.zip`.

After copying that Worker into `~/arena-edge`, run:

```bash
cd ~/arena-edge
npx wrangler deploy
```

Otherwise the old Worker will continue banning IP addresses even after the website files are updated.

## Existing uploads
Do not delete your current `/var/www/sports-live/uploads/teams/` images when deploying. The update only changes the `.htaccess` in that folder.


## v2.1 hotfix
- Main Arena 4K image reduced substantially on desktop and mobile.
- Added Telegram channel button: https://t.me/+9Akzb5efjaNlMjA8
- Hidden admin panel no longer inherits the public country/device maintenance restriction.
  Use the latest generated panel-<32hex>.php URL.


## v2.2 UI hotfix
- Main logo changed to small circular avatar style.
- Site title / heading reduced for a cleaner compact hero area.
- Telegram button redesigned with Telegram-blue pill background and a paper-plane icon.


## v2.3 compact header
- Logo reduced to a true small circular avatar (72px desktop / 64px mobile).
- Removed duplicated ARENA 4K overline and reduced site title size.
- Rebuilt Telegram CTA as a professional Telegram-blue card button with authentic paper-plane SVG, title, subtitle, and arrow.
