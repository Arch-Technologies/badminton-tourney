# Deploying to DreamHost Shared Hosting

This app is deliberately built to avoid anything DreamHost shared hosting can't
do: no database server (SQLite is just a file), no Node/npm on the server
(Tailwind/JS assets are built locally and committed), no queue workers, no
Redis. Everything runs as plain PHP-FPM + SQLite behind Apache.

## One-time DreamHost setup

1. **Enable SSH access** for your DreamHost (shell) user, if not already on
   (panel.dreamhost.com → Users → Manage Users → edit user → enable SSH).
2. **Pick a PHP version.** Go to *Manage Domains* → your domain → *PHP
   version* and select **8.3 or newer** (Laravel 13 requires PHP ^8.3; this
   app was built and tested against 8.4).
3. **Point the domain's web directory at `public/`, not the app root.**
   In *Manage Domains* → edit the domain, set **Web Directory** to:
   ```
   badminton-tourney/public
   ```
   (assuming you'll clone the app into `~/badminton-tourney` — see below).
   This is the key trick for Laravel on DreamHost: it keeps `app/`, `.env`,
   `database/`, `vendor/`, etc. completely outside the web-servable path, so
   nothing except what's in `public/` is ever reachable over HTTP.
4. **Enable free SSL** for the domain (same screen, "Let's Encrypt SSL").

## First deploy

SSH into the server, then:

```bash
cd ~
git clone <your-repo-url> badminton-tourney
cd badminton-tourney

composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
APP_NAME="ShuttleSync"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=sqlite
# DB_DATABASE can stay unset — Laravel defaults it to database/database.sqlite
```

`SESSION_DRIVER=database`, `CACHE_STORE=database` and `QUEUE_CONNECTION=database`
are already the defaults in `.env.example` — leave them as-is. This app never
dispatches queued jobs, so no `queue:work` process is needed even though the
`jobs` table exists.

Create the SQLite file and run migrations:

```bash
touch database/database.sqlite
php artisan migrate --force
```

If you want the demo tournament to see how everything works, run
`php artisan db:seed --force` once; otherwise skip it and create your first
organizer account directly (see "Creating an organizer account" below).

Make sure the app can write to the two directories it needs:

```bash
chmod -R 775 storage bootstrap/cache
```

Cache the framework for production performance:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

That's it — visit `https://your-domain.com` and you should see the public
tournament list.

## Deploying updates

Because DreamHost shared hosting has no Node/npm, **build assets locally**
before every deploy and commit the compiled output in `public/build/`
(this repo's `.gitignore` is set up to track it, unlike a typical Laravel
project).

Locally:

```bash
npm run build
git add -A
git commit -m "..."
git push
```

On the server:

```bash
cd ~/badminton-tourney
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize   # re-caches config/routes/views
```

## Creating an organizer account

There's no public "sign up as organizer" flow by design — anyone who can
register would otherwise be able to manage every tournament. Create the
first account from the server shell instead:

```bash
php artisan tinker
>>> \App\Models\User::create(['name' => 'Your Name', 'email' => 'you@example.com', 'password' => bcrypt('a-strong-password')]);
```

Log in at `/login`; the nav bar's "Tournaments" / "Players" links are the
organizer area (everything else under `/tournaments` is the public site).

## Why these choices fit DreamHost shared hosting

- **SQLite, not MySQL** — one file in `database/`, no separate DB server or
  credentials to provision, and it's outside the web root so it's never
  downloadable.
- **No queue workers** — DreamHost shared hosting doesn't give you a
  long-running process for `queue:work`. Everything (bracket generation,
  score recording) runs synchronously in the request, which is plenty fast
  for tournament-sized data.
- **File-based/database session & cache** — no Redis/Memcached needed.
- **Pre-built frontend assets** — no Node on the server, so Tailwind/Alpine
  are compiled locally and the `public/build/` output is committed to git.
- **`public/` as the web root** — the standard, secure way to run Laravel
  on a host where you don't control the Apache vhost config directly.

## Optional: scheduled tasks

This app doesn't currently need `php artisan schedule:run`, but if you add
something that does, DreamHost's panel has a **Cron Jobs** section
(under *Goodies*) where you can add:

```
* * * * * cd ~/badminton-tourney && php artisan schedule:run >> /dev/null 2>&1
```
