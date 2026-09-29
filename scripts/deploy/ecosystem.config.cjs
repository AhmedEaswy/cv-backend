/**
 * PM2 process file: Nuxt SSR server, Laravel queue worker, Laravel scheduler.
 *
 * One-time on the VPS:
 *   npm i -g pm2
 *   cd /var/www/.../cv.edgesgate.com
 *   pm2 start scripts/deploy/ecosystem.config.cjs
 *   pm2 save && pm2 startup
 *
 * Deploy workflow runs: pm2 startOrReload scripts/deploy/ecosystem.config.cjs
 *
 * Sidebase Auth.js reads NUXT_AUTH_* / NUXT_GOOGLE_* at runtime. Values are
 * loaded from the app root .env and frontend/.env.
 */
const fs = require('fs');
const path = require('path');

function loadEnvFile(filePath) {
  const out = {};
  if (!fs.existsSync(filePath)) {
    return out;
  }

  for (const raw of fs.readFileSync(filePath, 'utf8').split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith('#')) {
      continue;
    }
    const eq = line.indexOf('=');
    if (eq <= 0) {
      continue;
    }
    const key = line.slice(0, eq).trim();
    let value = line.slice(eq + 1).trim();
    if (
      (value.startsWith('"') && value.endsWith('"')) ||
      (value.startsWith("'") && value.endsWith("'"))
    ) {
      value = value.slice(1, -1);
    }
    out[key] = value;
  }

  return out;
}

const rootDir = path.join(__dirname, '../..');
const rootEnv = loadEnvFile(path.join(rootDir, '.env'));
const frontEnv = loadEnvFile(path.join(rootDir, 'frontend/.env'));
const merged = { ...rootEnv, ...frontEnv };

const appUrl = (merged.APP_URL || 'https://cv.edgesgate.com').replace(/\/+$/, '');

const phpBinary = merged.PM2_PHP_BINARY || 'php';

// Laravel processes must run as the PHP-FPM pool user, otherwise files they
// create in storage/ (logs, cache) become root-owned and break the website.
// PM2 can only switch user when it runs as root.
const laravelUser = merged.PM2_LARAVEL_USER || 'edgesgate_co_usr';
const isPm2Root = typeof process.getuid === 'function' && process.getuid() === 0;
const laravelUserOptions = isPm2Root && laravelUser ? { uid: laravelUser, gid: laravelUser } : {};

module.exports = {
  apps: [
    {
      name: 'cv-nuxt',
      cwd: path.join(rootDir, 'frontend'),
      script: '.output/server/index.mjs',
      instances: 1,
      exec_mode: 'fork',
      env: {
        NODE_ENV: 'production',
        HOST: '127.0.0.1',
        PORT: 3001,
        // Same-origin behind nginx — Nuxt public config was baked at build time
        // via NUXT_PUBLIC_LARAVEL_URL. Keep NITRO_PORT for Nitro compatibility.
        // Port 3001: 3000 is already used by another site on this VPS (gcschool).
        NITRO_HOST: '127.0.0.1',
        NITRO_PORT: 3001,
        NUXT_PUBLIC_LARAVEL_URL: merged.NUXT_PUBLIC_LARAVEL_URL || appUrl,
        NUXT_PUBLIC_APP_NAME: merged.NUXT_PUBLIC_APP_NAME || merged.APP_NAME || 'CV',
        NUXT_SITE_URL: merged.NUXT_SITE_URL || appUrl,
        NUXT_AUTH_ORIGIN: merged.NUXT_AUTH_ORIGIN || `${appUrl}/api/auth`,
        NUXT_AUTH_SECRET: merged.NUXT_AUTH_SECRET || merged.AUTH_SECRET || '',
        NUXT_GOOGLE_CLIENT_ID:
          merged.NUXT_GOOGLE_CLIENT_ID || merged.GOOGLE_CLIENT_ID || '',
        NUXT_GOOGLE_CLIENT_SECRET:
          merged.NUXT_GOOGLE_CLIENT_SECRET || merged.GOOGLE_CLIENT_SECRET || '',
      },
      max_memory_restart: '512M',
      time: true,
    },
    {
      name: 'cv-queue',
      cwd: rootDir,
      script: 'artisan',
      interpreter: phpBinary,
      // --timeout must stay below DB_QUEUE_RETRY_AFTER (90s) so a job is never run twice.
      // --max-time recycles the worker hourly to release memory.
      args: 'queue:work database --queue=default --sleep=3 --tries=3 --backoff=10 --timeout=75 --max-time=3600',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      // Lets the in-flight job finish on reload (SIGTERM is handled gracefully by the worker).
      kill_timeout: 90000,
      max_memory_restart: '256M',
      time: true,
      ...laravelUserOptions,
    },
    {
      name: 'cv-scheduler',
      cwd: rootDir,
      script: 'artisan',
      interpreter: phpBinary,
      args: 'schedule:work',
      instances: 1,
      exec_mode: 'fork',
      autorestart: true,
      max_memory_restart: '128M',
      time: true,
      ...laravelUserOptions,
    },
  ],
};
