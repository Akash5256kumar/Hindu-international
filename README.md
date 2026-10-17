# Hindu.International — Website (Virtual Punya Snan service)

HTML5 / CSS3 / JavaScript website with a small PHP admin panel for editing content and images. Built for Hostinger (PHP 8.0+).

## Pages
`index.html` · `about.html` · `how-it-works.html` (with FAQ) · `protect-our-faith.html` · `donate.html` · `event.html` · `booking.html` · `contact.html` · `privacy.html` · `terms.html`

## Donations (`donate.html`)
- **UPI works directly:** set the UPI ID, payee name and upload the UPI QR code in *Admin → Donate → UPI payment details*. Donors see the QR / UPI ID, and on phones a “Pay with a UPI app” button with the amount filled in.
- **Card / Net Banking / Wallet** need a payment gateway (e.g. Razorpay) connected to the organisation's merchant account. Until then these donations are recorded and emailed to you, and the donor is told a secure payment link will follow.
- Every donation (name, email, phone, amount, method) is emailed to `VPS_RECIPIENT` in `forms/config.php`.

## Project structure
```
index.html … contact.html   Pages (layout + default text)
css/                        style.css (design tokens, components) · responsive.css · faith.css (Protect Our Faith) · roots.css (Donate)
js/components.js            Header, navigation, footer, icon set (shared by all pages)
js/script.js                Fills pages from content, menu, uploads preview, forms
js/seo.js                   Applies admin-managed page titles, descriptions, canonical and social metadata
js/donate.js                Donation form: amounts, payment method, UPI payment step
content/content.json        Editable text, images, events, SEO metadata and contact details (source of truth)
content/content.js          Generated from content.json — loaded by the pages. Do not edit by hand.
assets/                     Built-in images, icons, videos
uploads/                    Files uploaded through the admin panel
forms/                      submit.php (sends form emails) · config.php (recipient)
admin/                      Admin panel (see below)
```

## Admin panel — `/admin/`
Edit every page's text, the events, FAQ, contact details, social links, site colours, card icons and icon colours, donation amounts and UPI details, photos (JPG, PNG, WebP, SVG) and videos (MP4, WebM). Changes go live as soon as you click **Save changes**.

Also editable without a developer:
- **Header, footer & colours:** logo text, tagline and size, an optional logo image (plus a light version for the footer), menu items (label, link, order, show/hide, add/remove), the header button text and link, footer headings, footer links and the copyright line.
- **Button text and links** on the Home and Protect Our Faith pages. A link can be a page (`donate.html`, `donate.html#donate-form`), a section (`#anti-conversion`), a full `https://` link, `mailto:` or `tel:`. An empty button text hides the button.
- **Page layout** (Home, Protect Our Faith): show or hide each section and change the order. On Protect Our Faith you can also add **custom sections** (label, heading, text, button, photo); the section numbers 01, 02 … follow the order automatically.
- **On-page SEO** for every public page: browser/search title, publisher metadata, meta description, focus-keyword notes, canonical URL, index/follow controls, an `X-Robots-Tag` response header and social-sharing title, description and image. Privacy and Terms pages remain noindex by default. Search engines do not use meta keywords or the publisher meta tag as ranking signals.
- **Cards and list items** can be added, removed, reordered and switched off individually.

**First-time setup (once per server)**
1. Open `https://your-domain/admin/`. You are sent to the setup page.
2. Enter the **setup key** from the private server configuration, choose a username and a password (10+ characters).
3. Log in. Setup is switched off automatically after the login exists.

On the VPS, keep the production settings in `/etc/hindu.international/admin.php` and `/etc/hindu.international/forms.php`, outside the web root. Never upload these settings or `admin/config.php` / `forms/config.php` to the public web root.

**How it works**
- `admin/includes/schema.php` defines every editable field. The editor form and the server validation are both generated from it, so to make a new piece of text editable: add the field to the schema, the default value to `content/content.json`, and `data-bind="path.to.field"` to the element in the HTML.
- Binding attributes used in the pages: `data-bind` (text), `data-bind-paras` (paragraphs), `data-bind-src` / `data-bind-alt` (images), `data-bind-href` (button links, validated), `data-show-if` (hide when the value is empty / off), `data-bind-list` + `<template>` (repeating items such as the FAQ; items with `show: false` are skipped), `data-bind-icon` (icon name), `data-bind-color` (icon colour).
- Section layout: `<main data-layout="faith.layout">` holds one element per section with `data-block="name"`; the `layout` list in the content decides which are shown and their order. `[data-block-num]` is filled with the section's position. To add a new kind of section to a page: add the markup with a new `data-block` name, add the name to that page's `$layout([...])` options in `schema.php`, and add its fields as a new group.
- Theme colours are applied by `js/components.js` as CSS variables, so `content/content.js` is loaded in `<head>` on every page.
- Each save keeps a backup of the previous content in `admin/data/backups/` (last 20).

**Security**
- Production admin credentials, login attempts and content backups are stored outside the web root in `/var/lib/hindu.international/admin-data/`. Passwords are stored only as hashes; login locks for 15 minutes after 5 wrong attempts; session expires after 2 hours idle.
- CSRF protection on every change, strict Content-Security-Policy, the admin cannot be framed, HTTPS enforced.
- Uploads are checked by their real file type, saved with random names, and cannot be executed (`uploads/.htaccess`). SVG files are rebuilt from an allow-list, which removes scripts, event handlers and external links.
- `admin/includes` and `admin/data` are blocked from the web (`.htaccess`).

**Forgot the password?** On the VPS, remove `/var/lib/hindu.international/admin-data/credentials.php` and run the setup again.

## Forms
Both forms post to `forms/submit.php`, which validates the input and emails it to `VPS_RECIPIENT` (the booking form attaches the photograph; nothing is stored on the server). Before going live:
1. Set `VPS_RECIPIENT` and `VPS_FROM` in the private production config at `/etc/hindu.international/forms.php`. `VPS_FROM` must be a mailbox configured to send mail for your domain.
2. Send a test request from the live site and check it arrives, including the photo.

No payment or order system is included (not confirmed in the BRD).

## Deploying to Hostinger
1. Upload the public site and application code (including all `.htaccess` files) to the web root. Keep admin/form configuration and admin runtime data outside the web root as described above.
2. Make sure PHP 8.0 or newer is selected (hPanel → Advanced → PHP Configuration).
3. The folders `content/`, `uploads/` and `admin/data/` must be writable by PHP (they are by default on Hostinger).
4. For large videos, raise `upload_max_filesize` and `post_max_size` in PHP Configuration (the admin allows up to 60 MB; change `max_video_mb` in `admin/config.php`).
5. Run the admin setup (above).

## Automatic deploy (GitHub Actions)
Every push to `main` runs `.github/workflows/deploy.yml`:
1. Checks every PHP file for syntax errors and checks that `content/content.json` is valid. If anything fails, nothing is deployed.
2. Copies the files to the VPS over SSH with rsync. Nothing on the server is deleted, so `uploads/` and `admin/data/` stay as they are.
3. Never uploads `README.md`, the BRD PDF, `.github/`, `.vscode/`, `admin/config.php` or `forms/config.php`. The live configuration lives in `/etc/hindu.international/` on the server.
4. Uploads `content/content.json` and `content/content.js` only when they are missing on the server, so admin panel edits are never overwritten. To push the repo's content live on purpose: GitHub → Actions → "Deploy to server" → Run workflow → tick `overwrite_content`. The live files are first copied to `~/hindu-content-backups/<date>/` on the server.

One-time setup:
1. Create a deploy key on your computer: `ssh-keygen -t ed25519 -f hindu_deploy -N "" -C github-deploy`
2. Add the contents of `hindu_deploy.pub` to `~/.ssh/authorized_keys` of the deploy user on the VPS (or hPanel → VPS → Settings → SSH keys).
3. In GitHub → Settings → Secrets and variables → Actions, add `SSH_HOST` (VPS IP), `SSH_USER` (e.g. `root`) and `SSH_PRIVATE_KEY` (the whole `hindu_deploy` file). Optional: `SSH_PORT` (default 22) and `DEPLOY_PATH` (default `/var/www/html/hindu.international/public`).
4. Make sure rsync is installed on the VPS: `apt install -y rsync`
5. Remove the old webhook (GitHub → Settings → Webhooks) and delete `deploy.php` from the server.

New files get the same owner as the server's `content/` folder, so PHP can keep writing to it. After the first deploy, save something in the admin panel to confirm it still works.

## Running locally
PHP is needed for the admin panel and forms:
```
php -S 127.0.0.1:8000
```
Then open http://127.0.0.1:8000 (site) and http://127.0.0.1:8000/admin/ (admin). The PHP built-in server ignores `.htaccess`, so the folder protections only apply on Hostinger/Apache. Forms cannot send email locally.

## Before launch
- The content/ folder holds the live content. When you upload this update, upload `content/content.json` and `content/content.js` **once**: they contain the new Protect Our Faith page, menu and footer settings, and remove the old Preserve Our Roots content. Make any changes made on the live admin since the last sync again afterwards, or merge them in first. After that, edit content only in the admin.
- `preserve-our-roots.html` now redirects to `protect-our-faith.html` (`.htaccess` 301), so old links keep working.
- Photos are cut from the design mockup and upscaled, so they are soft on large screens. Upload the original high-resolution images through the admin panel.
- Fill in every `[placeholder]` (event, contact details, FAQ answers) through the admin panel.
- Finalise `privacy.html` and `terms.html`, then remove their `noindex` tag.
