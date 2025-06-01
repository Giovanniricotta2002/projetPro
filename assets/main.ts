import { createApp } from 'vue'
import App from './App.vue'

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// Pinia (facultatif)
import { createPinia } from 'pinia'

// Vue Router (auto or classic)
import { createRouter, createWebHistory } from 'vue-router/auto'

const app = createApp(App)

const vuetify = createVuetify({
  components,
  directives,
})

app.use(vuetify)
app.use(createPinia())
app.use(createRouter())

app.mount('#app')