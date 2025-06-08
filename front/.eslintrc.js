module.exports = {
  root: true,
  env: {
    browser: true,
    es2021: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:import/recommended',
    'plugin:import/typescript',
    'plugin:perfectionist/recommended-natural',
    'prettier',
  ],
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: '@typescript-eslint/parser',
    ecmaVersion: 2021,
    sourceType: 'module',
  },
  plugins: [
    'vue',
    '@typescript-eslint',
    'import',
    'perfectionist',
  ],
  rules: {
    'import/no-duplicates': [
      'error',
      {
        considerQueryString: true, // autorise auto vs auto-routes
      },
    ],
    'perfectionist/sort-imports': [
      'error',
      {
        type: 'alphabetical',
        order: 'asc',
        groups: [
          'builtin', // node:url, path, etc.
          'external', // vue, vue-router
          'internal', // @/components etc.
          'parent',
          'sibling',
          'index',
          'type',
        ],
      },
    ],
  },
}
