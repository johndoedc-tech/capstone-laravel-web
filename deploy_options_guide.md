# Capstone Project Deployment Guide

Because you have two distinct tools that talk to each other (Laravel PHP and Machine Learning Python), deploying to the web requires setting up both services and connecting them. I noticed both repositories already have `Procfile`s, which means they are perfectly configured for Cloud Platforms like **Render**, **Railway**, or **Heroku**.

Here are the two primary ways to deploy your systems depending on your budget and preference.

---

## 🌟 Option 1: PaaS (Platform as a Service) - Recommended
*Best if you want free/cheap hosting and easy "Push to Deploy". Recommended services: **Render.com** or **Railway.app**.*

### Step 1: Upload your code to GitHub
Make sure both of your folders are uploaded to their own separate GitHub repositories.
1. `capstone-ml-api` (For the Machine Learning folder)
2. `capstone-laravel-web` (For the BACKUP1 folder)

### Step 2: Deploy the Machine Learning API first
We deploy the ML API first so we can grab its public URL and feed it to Laravel.
1. On **Render.com**, click **New > Web Service**.
2. Connect your `capstone-ml-api` GitHub repository.
3. Choose the **Python** environment.
4. Render will automatically detect the `requirements.txt` and `Procfile`.
   - **Start Command (if asked):** `gunicorn ml_api:app --bind 0.0.0.0:$PORT`
5. Click **Deploy**.
6. Once deployed, copy your new ML URL (e.g., `https://capstone-ml-xyz.onrender.com`).

### Step 3: Set up a Cloud Database (MySQL)
Laravel needs a live database, not `localhost`.
1. You can use **Aiven.io**, **Railway.app**, or **TiDB Cloud** to get a free/cheap MySQL database.
2. Once created, copy the database credentials (`Host`, `Port`, `Username`, `Password`, `Database Name`).

### Step 4: Deploy the Laravel Project
1. Go back to Render.com and click **New > Web Service**.
2. Connect your `capstone-laravel-web` repository.
3. Choose the **PHP** environment.
4. Under Environment Variables (`.env`), add these:
   - `APP_ENV=production`
   - `APP_URL=https://your-laravel-app-url.onrender.com`
   - `DB_HOST=` *(from Step 3)*
   - `DB_DATABASE=` *(from Step 3)*
   - `DB_USERNAME=` *(from Step 3)*
   - `DB_PASSWORD=` *(from Step 3)*
   - `ML_URL_API=` *(Paste the URL from Step 2, e.g., `https://capstone-ml-xyz.onrender.com` without the `/` at the end)*
5. Click Deploy. 

*(Note: Don't forget to run your migrations using `php artisan migrate` in your hosting platform's terminal/console!)*

---

## 💻 Option 2: VPS (Virtual Private Server) or Ubuntu Server
*Best if you bought a Hostinger VPS or DigitalOcean droplet and want to host them both in one machine.*

### Step 1: Prepare the Server
1. SSH into your Ubuntu server.
2. Install **Nginx / Apache**, **PHP 8.2**, **MySQL/MariaDB**, **Python 3**, and **Composer**.
3. Create a MySQL database and user.

### Step 2: Deploy Machine Learning API
1. Move the `Machine Learning - capstone` folder to your server (e.g., `/var/www/ml-api`).
2. Create a virtual environment:
   ```bash
   python3 -m venv venv
   source venv/bin/activate
   pip install -r requirements.txt
   ```
3. Run it in the background using `gunicorn` (using a tool like `supervisor` or `systemd`).
   - E.g., running it on internal port `5000`.

### Step 3: Deploy Laravel
1. Move the `BACKUP1` folder to your server (e.g., `/var/www/laravel`).
2. Run `composer install --optimize-autoloader --no-dev`.
3. Configure your `.env`. Set the DB credentials to the ones you made in Step 1.
4. Set the `ML_URL_API` in the `.env` file to your server's internal localhost running the ML (e.g., `http://127.0.0.1:5000`).
5. Set up **Nginx** or **Apache** to point requests on port `80` to `/var/www/laravel/public`.
6. Apply database migrations: `php artisan migrate`.

---

### Which option would you like me to elaborate on?
If you have a server in mind (like Hostinger, cPanel, Railway, Heroku, or Render), let me know and I can show you exactly how to do it step-by-step for that specific provider!
