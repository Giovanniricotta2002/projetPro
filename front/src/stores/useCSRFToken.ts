// Utilities
import {apiEndpoint, corsRequestHeaders} from '@/config';
import { defineStore } from 'pinia'

export const useCSRFToken = defineStore('csrf_tocken', {
  state: () => ({
      token: '' as string,
      loading: false as boolean,
      error: null as string | null,
  }),
  actions: {
    async fetchCSRFToken() {
      
      this.loading = true;
      this.error = null;

      try {
        const url = new URL('/api/csrfToken', `${apiEndpoint}`)

        const response = await fetch(url, {
          method: 'GET',
          credentials: 'include',
          headers: corsRequestHeaders,
        });

        if (!response.ok) {
          throw new Error('Failed to fetch CSRF token');
        }

        const data = await response.json();
        
        this.token = data.csrfToken || '';
      } catch (error) {
        this.error = error instanceof Error ? error.message : 'An unknown error occurred';
      }
      this.loading = false;
    }
  }
})
