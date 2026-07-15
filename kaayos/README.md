# KaAyos

A home services platform connecting clients with verified workers in Tuy, Batangas. Clients can book services across categories like Plumbing, Electrical, Cleaning, Carpentry, Painting, and Aircon.

## Seeded Test Accounts

All accounts use the password: `password`

| Role    | Email                  | Name              |
|---------|------------------------|-------------------|
| Admin   | admin@kaayos.com       | Admin KaAyos      |
| Client  | maria@example.com      | Maria Santos      |
| Client  | john@example.com       | John Villanueva   |
| Worker  | juan@example.com       | Juan Dela Cruz (Plumbing) |
| Worker  | elena@example.com      | Elena Santos (Cleaning) |
| Worker  | marco@example.com      | Marco Reyes (Electrical) |

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database credentials, then:

```bash
php artisan migrate
php artisan db:seed
```

## Realtime Notifications & Chats (Laravel Reverb)

Start the WebSocket server for realtime features:

```bash
php artisan reverb:start
```

For production or external access:

```bash
php artisan reverb:start --port=8080 --host=0.0.0.0
```
