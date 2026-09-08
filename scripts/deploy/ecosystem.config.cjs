/**
 * PM2 process file for the Nuxt SSR server.
 *
 * One-time on the VPS:
 *   npm i -g pm2
 *   cd /var/www/.../cv.edgesgate.com
 *   pm2 start scripts/deploy/ecosystem.config.cjs
 *   pm2 save && pm2 startup
 *
 * Deploy workflow runs: pm2 startOrReload scripts/deploy/ecosystem.config.cjs
 */
module.exports = {
  apps: [
    {
      name: 'cv-nuxt',
      cwd: __dirname + '/../../frontend',
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
      },
      max_memory_restart: '512M',
      time: true,
    },
  ],
};
