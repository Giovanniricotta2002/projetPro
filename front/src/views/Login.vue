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
                    <v-btn class="mt-2" type="submit" block :disabled="!valid" @click="fLogin">Se connecter</v-btn>
                    <v-btn class="mt-2" type="submit" block @click="redirect">Cree un compte</v-btn>
                    <input type="hidden" name="_csrf_token" v-model="csrf_token">
                    <v-alert v-if="error" type="error" class="mt-2">{{ error }}</v-alert>
                </v-form>
            </div>
        </div>
    </v-app>
</template>

<script setup lang="ts">
import { useRuleInput } from '@/stores/ruleInput'
import { useCSRFToken } from '@/stores/useCSRFToken'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const login = ref('')
const password = ref('')
const error = ref('')
const version = ref('0.0.1')
const title = ref('Se connecter')
const csrf_token = ref('')
const valid = ref(false)
const show1 = ref(false)
const router = useRouter()

const rules = useRuleInput()


async function fLogin() {
    const body = {
        login: login.value,
        password: password.value
    }

    localStorage.setItem('token', 'true')
    router.push('/')
    
    /*try {
        const response = await fetch('/exemple.com/api/login', {
            method: 'POST',
            credentials: 'include',
            body: JSON.stringify(body)
        });
            
        if (!response.ok) {
            const error = await response.json()
            throw new Error(error.message || 'Erreur de connection');
        }
                
        localStorage.setItem('token', csrf_token.value)
        router.push('/')
    } catch (err: any) {
        error.value = err.message
    }*/
}


const redirect = () => {
    router.push('/register')
}

const csrfToken = useCSRFToken()

onMounted(() => {
    csrfToken.fetchCSRFToken()
    csrf_token.value = csrfToken.token
})

const formRef = ref()

declare global {
  interface Window {
    login: typeof formRef
  }
}
// ⬇️ Exposé dans la console navigateur
window.login = {
    login,
    password,
    error,
    version,
    title,
    csrf_token,
    valid,
    show1,
    router,
    rules,
    fLogin,
    redirect,
    useCSRFToken,
} as any

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
