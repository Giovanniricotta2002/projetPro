<template>
    <v-app>
        <v-app-bar dense flat max-height="3em">
            <p class="version">{{ version }}</p>
            <v-toolbar-title>{{ title }}</v-toolbar-title>
        </v-app-bar>
        <div class="login">
            <div class="form">
                <v-form ref="form" v-model="valid" @submit.prevent="newUser">
                    <v-container>
                        <v-row>
                            <v-col>
                                <v-text-field 
                                    label="Username" 
                                    v-model="username"
                                    required
                                    :rules="[ruleInput.loginRule]">
                                </v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <v-text-field 
                                    label="Email" 
                                    v-model="email"
                                    type="email"
                                    required
                                    :rules="[ruleInput.emailRule]">
                                </v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="6">
                                <v-text-field 
                                    label="Password" 
                                    v-model="password"
                                    type="password"
                                    required
                                    :rules="[ruleInput.passwordRule]">
                                </v-text-field>
                            </v-col>
                            <v-col cols="6">
                                <v-text-field 
                                    label="Confirmer le password" 
                                    v-model="confirmPassword"
                                    type="password"
                                    required
                                    :rules="confirmPasswordRules">
                                </v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12">
                                <v-btn 
                                    type="submit" 
                                    variant="elevated" 
                                    color="primary"
                                    :disabled="!valid || auth.isLoading.value"
                                    block>
                                    {{ auth.isLoading ? 'Création...' : 'Créer le compte' }}
                                </v-btn>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <v-alert v-if="auth.error" type="error" class="mt-2">{{ auth.error }}</v-alert>
                            </v-col>
                        </v-row>
                    </v-container>
                    <input type="hidden" name="_csrf_token" v-model="csrfToken.token">
                </v-form>
            </div>
        </div>
    </v-app>
</template>

<script setup lang="ts">
import { useCSRFToken } from '@/stores/useCSRFToken'
import { useRuleInput } from '@/stores/ruleInput'
import { useAuth } from '@/composables/useAuth'
import { ref, onMounted, computed } from 'vue'

const version = ref('0.0.1')
const title = ref('Créer un compte')
const valid = ref(false)
const username = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const ruleInput = useRuleInput()
const auth = useAuth()
const csrfToken = useCSRFToken()

// Règles de validation personnalisées pour la confirmation de mot de passe
const confirmPasswordRules = computed(() => [
    (v: string) => ruleInput.confirmPasswordRule(v, password.value)
])

async function newUser() {
    if (!valid.value) {
        return
    }

    await auth.registerAndRedirect({
        username: username.value,
        email: email.value,
        password: password.value,
        csrfToken: csrfToken.token,
    })
}

onMounted(async () => {
    // Rediriger si déjà connecté
    auth.requireGuest()
    
    await csrfToken.fetchCSRFToken()
})
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
