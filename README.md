# Forum

A small forum I'm building with a Laravel API and a Vue 3 frontend, kept as two
separate projects so either side can be swapped out or deployed on its own.

- `backend/` - Laravel 13, API only. MySQL, Passport for auth, database driver
  for cache/queue so there's nothing extra to install.
- `frontend/` - Vue 3 + Vite SPA with Pinia, Vue Router and Tailwind.

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
