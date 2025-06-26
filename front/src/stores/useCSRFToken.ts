// Utilities
import { defineStore } from 'pinia'

export const useCSRFToken = defineStore('csrf_tocken', {
  state: () => ({
      token: '' as string,
      loading: false as boolean,
      error: null as string | null,
  }),
  actions: {
    async fetchCSRFToken() {
      console.log("fezfzefzefze");
      
      this.loading = true;
      this.error = null;

      try {
        const url = new URL('/api/csrfToken', 'http://backend:80/')

        const response = await fetch(url, {
          method: 'GET',
          credentials: 'include',
        });

        if (!response.ok) {
          throw new Error('Failed to fetch CSRF token');
        }

        const data = await response.json();
        this.token = data.token || '';
      } catch (error) {
        this.error = error instanceof Error ? error.message : 'An unknown error occurred';
      }
      this.loading = false;
    }
  }
})
