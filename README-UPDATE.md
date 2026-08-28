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
