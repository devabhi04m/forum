# Forum API

The backend half of the forum. Laravel 13, API only - no Blade, no asset
pipeline. The Vue SPA in `../frontend` is the only client.

## Stack notes

- MySQL, database name `forum`
- Auth is Laravel Passport with the password grant. The SPA exchanges
  email/password for a Bearer token at `/oauth/token`.
- Cache, queue and sessions all use the `database` driver, so no Redis.
- Forum code lives in `app/Modules/Forum/` (entities, controllers, requests,
  resources, policies) instead of the default app structure.

## Setup

```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan passport:client --password
php artisan serve --port=8973
```

The `passport:client` command prints a client id and secret - those go into the
frontend's `.env` as `VITE_OAUTH_CLIENT_ID` / `VITE_OAUTH_CLIENT_SECRET`.

`ForumSeeder` gives you a few categories, tags, threads and posts to click
around in. Seeded users all have the password `password`.

## Routes

Everything is under `/api`. Reads (categories, threads, posts, tags) are
public. Writes need a Bearer token, and banned accounts get 403 on all of
them. See `routes/api.php`.

## Roles

Users have a `role` column: `user`, `moderator` or `admin`. Mods can
pin/lock/hide threads, delete anything, and work the report queue at
`/api/forum/moderation/*`. Admins additionally manage users (ban, promote)
and categories. Promote someone with tinker:

```
php artisan tinker --execute="App\Models\User::where('email','you@example.com')->first()->forceFill(['role'=>'admin'])->save();"
```

## Notifications

Replies, @mentions, followed-thread activity and report outcomes create
database notifications, dispatched through the queue - so a queue worker has
to be running to see them (`composer dev` runs one, or `php artisan
queue:work`).
