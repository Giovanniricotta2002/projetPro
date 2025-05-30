import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  root: 'assets',
  build: {
    outDir: '../public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'assets/js/app.js'
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './assets/js'),
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    origin: 'http://localhost:5173',
    proxy: {
      '^/(?!build|_vite|assets).*': 'http://localhost:8000',
    },
  }
})
