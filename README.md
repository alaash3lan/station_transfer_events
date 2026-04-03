# Laravel Docker Environment

This project uses a multi-stage Docker setup to provide a seamless transition between local development and production deployment.

## Prerequisites
- Docker installed
- Docker Compose installed

---

## 💻 Local Development
In development, we use **bind mounts**. This means your code changes on your host machine are reflected instantly inside the container without needing a rebuild.

**Start the environment:**
```bash
docker compose up -d
```

## Common Development Commands:

- View Logs:``` docker compose logs -f app ```

 - Run Artisan:``` docker compose exec app php artisan <command> ```

- Access Container: ``` docker compose exec app bash ```

- Rebuild (if composer.json changes): ``` docker compose up -d --build ```


## Production Deployment
In production, we use the production override. This disables debug mode, removes live code syncing (using the code "baked" into the image), and ensures the container restarts automatically if it crashes.

## Start the production environment:
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

