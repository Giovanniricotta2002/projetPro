<template>
    <v-app>
        <v-app-bar dense flat max-height="3em">
            <p class="version">{{ version }}</p>
            <v-toolbar-title class="">{{ title }}</v-toolbar-title>
        </v-app-bar>
        <div class="login">
            <div class="form">
                <v-form ref="form" v-model="valid" @submit.prevent="fLogin">
                    <v-text-field label="Login" v-model="login" @keydown.enter="fLogin" required name="login" :rules="[rules.loginRule]"></v-text-field>
                    <v-text-field label="Password" v-model="password" required @keydown.enter="fLogin" :type="show1 ? 'text' : 'password'" name="password" :rules="[rules.passwordRule]"></v-text-field>
                    <v-btn class="mt-2" type="submit" block :disabled="!valid">
                        {{ auth.isLoading ? 'Connexion...' : 'Se connecter' }}
                    </v-btn>
                    <v-btn class="mt-2" type="button" block @click="redirect">Créer un compte</v-btn>
                    <input type="hidden" name="_csrf_token" v-model="csrfToken.token">
                    <v-alert v-if="auth.error" type="error" class="mt-2">{{ auth.error }}</v-alert>
                </v-form>
            </div>
        </div>
    </v-app>
</template>

<script setup lang="ts">
import { useRuleInput } from '@/stores/ruleInput'
import { useCSRFToken } from '@/stores/useCSRFToken'
import { useAuth } from '@/composables/useAuth'
import { ref, onMounted } from 'vue'

const login = ref('')
const password = ref('')
const version = ref('0.0.1')
const title = ref('Se connecter')
const csrf_token = ref('')
const valid = ref(false)
const show1 = ref(false)

const rules = useRuleInput()
const auth = useAuth()
const csrfToken = useCSRFToken()

onMounted(async () => {
    // Rediriger si déjà connecté
    auth.requireGuest()
    
    await csrfToken.fetchCSRFToken()
    csrf_token.value = csrfToken.token
})

async function fLogin() {
    await auth.loginAndRedirect({
        login: login.value,
        password: password.value,
        csrfToken: csrfToken.token,
    })
}

const redirect = () => {
    auth.requireGuest('/register')
}



const formRef = ref()

// ⬇️ Exposé dans la console navigateur (dev uniquement)
declare global {
  interface Window {
    login: typeof formRef
  }
}
if (import.meta.env.DEV) {

  window.login = {
    login,
    password,
    version,
    title,
    csrf_token,
    valid,
    show1,
    rules,
    fLogin,
    redirect,
    auth,
    csrfToken,
  } as any
}

</script>

<style>
.v-toolbar__content{
    max-height: 50%;
    margin-top: 1%;
}
.v-toolbar{
    max-height: 12%;
}
.v-toolbar__title{
    margin-right: 47%;
    margin-left: auto;
}
img {
  width: 250px;
}
.login {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: space-between;
	margin: auto;
  width: 300px;
}
.form {
  width: 300px;
}
.version {
  display: none;
}
@media screen and (min-width: 600px){
  .login {
    width: 600px;
    flex-direction: row;
  }
  .version {
    display: contents;
  }
}
@keyframes barrelroll { 100% { transform: rotate(-360deg); } }
@-webkit-keyframes barrelroll { 100% { -webkit-transform: rotate(-360deg); } }
</style>
