# Forum

A small forum I'm building with a Laravel API and a Vue 3 frontend, kept as two
separate projects so either side can be swapped out or deployed on its own.

- `backend/` - Laravel 13, API only. MySQL, Passport for auth, database driver
  for cache/queue so there's nothing extra to install.
- `frontend/` - Vue 3 + Vite SPA with Pinia, Vue Router and Tailwind.

## What's in it

The forum side is the usual stuff done properly: categories (nested one level,
each with an optional emoji icon), threads with tags, replies, up/down votes,
bookmarks, thread follows and @mentions. There's search, user profiles, and
database notifications for replies, mentions, followed threads and report
outcomes. Anything can be reported, write actions are rate limited, and banned
accounts drop to read-only.

Moderators get a report queue, pin/lock/hide on any thread, and a stats
overview. All of that lives at `/moderation` inside the normal forum layout.

Admins get more: a separate full-screen panel at `/admin` with its own dark
sidebar, completely outside the forum chrome. From there you can manage users
(search, ban/unban, promote up to admin), every thread and post on the forum
(including soft-deleted ones, which can be restored or permanently removed),
categories with drag-free reordering via sort values, tags, and the same report
queue. The dashboard shows totals, weekly deltas, newest members and the
busiest categories.

There's also a dummy data tool under System in the admin sidebar. It generates
fake members who write threads and replies across your real categories, with
votes and spread-out timestamps so lists look lived-in. Every generated record
belongs to a user on a marker email domain, so the delete button removes all of
it in one go and can't touch real content.

## Roles

One `role` column on users: `user`, `moderator` or `admin`. Fresh registrations
are plain users. Promote the first admin from the command line:

```
php artisan tinker --execute="App\Models\User::where('email','you@example.com')->first()->forceFill(['role'=>'admin'])->save();"
```

After that you can promote everyone else from the admin panel.

## Running it

Backend (from `backend/`):

```
composer install
cp .env.example .env   # then fill in DB creds
php artisan key:generate
php artisan migrate --seed
php artisan passport:client --password
php artisan serve --port=8973
```

Frontend (from `frontend/`):

```
npm install
cp .env.example .env   # paste the passport client id/secret in here
npm run dev
```

The frontend dev server runs on 5273 and the API on 8973. If you change either
port, update `VITE_API_BASE_URL` in `frontend/.env` and `CORS_ALLOWED_ORIGINS`
in `backend/.env` so they keep pointing at each other.

Seeded users all come with the password `password`. The seeder drops in a few
categories, tags, threads and replies so the forum isn't empty on first run;
the dummy data tool in the admin panel can add more (or you can wipe its output
and start clean).

## API shape

Everything is under `/api`. Public reads for categories, threads, posts and
tags; a Bearer token for anything that writes. Moderation endpoints sit under
`/api/forum/moderation/*` and the admin ones under `/api/forum/admin/*` - the
role checks happen server-side in the controllers, the SPA only decides what to
show. See `backend/routes/api.php` for the full list, it's short enough to
read in one sitting.

Each half of the repo has its own README with more detail on its internals.
