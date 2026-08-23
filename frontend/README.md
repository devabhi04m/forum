# Forum frontend

Vue 3 SPA for the forum. Vite, Pinia, Vue Router, Tailwind 4, Axios. Talks to
the Laravel API in `../backend` over JSON, nothing server-rendered.

## Setup

```
npm install
cp .env.example .env
npm run dev
```

`.env` needs four values: the API base URL, the OAuth base URL, and the
password-grant client id/secret that `php artisan passport:client --password`
prints on the backend side.

## Layout

- `src/bootstrap.js` - shared axios instance, token handling, 401 logout
- `src/modules/forum/` - pages, components, store and API calls for the forum
- `src/modules/auth/` - login/register pages and the auth store
- `src/components/` - shared bits (header, avatar)

Styling is Tailwind with a couple of component classes (`card`, `input`,
`btn-primary`...) defined in `src/style.css`.
