// stores/ruleInput.ts
import { defineStore } from 'pinia'

export const useRuleInput = defineStore('rule_input', {
  state: () => ({}),

  getters: {
    loginRule: () => (v: string) => {
      if (!v) return 'Login must be at least 3 characters long'
      if (v.length > 180) return 'Login must be less than 180 characters'
      return true
    },

    passwordRule: () => (v: string) => {
      return !v || v.length < 6 ? 'Password must be at least 6 characters long' : true
    },

    emailRule: () => (v: string) => {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return !v || !emailPattern.test(v) ? 'Invalid email format' : true
    },

    urlRule: () => (v: string) => {
      const urlPattern = /^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?(\/[^\s]*)?$/
      return !v || !urlPattern.test(v) ? 'Invalid URL format' : true
    },
  },
})