import { defineConfig } from 'vite';

export default defineConfig({
  server: {
    host: '0.0.0.0',
    port: 4173,
    proxy: {
      '/api': {
        target: 'http://app:8000',
        changeOrigin: true,
      },
    },
  },
  preview: {
    host: '0.0.0.0',
    port: 4173,
  },
  test: {
    environment: 'jsdom',
  },
});
