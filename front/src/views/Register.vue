<template>
    <v-app>
        <v-app-bar dense flat max-height="3em">
            <p class="version">{{ version }}</p>
            <v-toolbar-title>{{ tilte }}</v-toolbar-title>
        </v-app-bar>
        <div class="login">
            <div class="form">
                <v-form ref="form" v-model="valid" @submit.prevent="newUser">
                    <v-container>
                        <v-row>
                            <v-col>
                                <v-text-field label="Username" required></v-text-field>
                            </v-col>
                            <v-col>
                                <v-text-field label="Mail" required></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <v-text-field label="Password" required></v-text-field>
                            </v-col>
                            <v-col>
                                <v-text-field label="Confirmer le password" required></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <v-btn>Crée</v-btn>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col>
                                <v-alert v-if="error" type="error" class="mt-2">{{ error }}</v-alert>
                            </v-col>
                        </v-row>
                    </v-container>
                    <input type="hidden" name="_csrf_token" v-model="csrf_token">
                </v-form>
            </div>
        </div>
    </v-app>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const version = ref('0.0.1')
const tilte = ref('Crée un compte')
const csrf_token = ref('')
const valid = ref(false)
const error = ref('')
const router = useRouter()

async function newUser() {
    const body = {
        username: '',
        mail: '',
        password: '',
    }

    try {
        const response = await fetch('/exemple.com/api/register', {
            method: 'POST',
            credentials: 'include',
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            error.value = await response.json()
            
            throw new Error(error.value || 'Erreur de connection');
        }

        router.push('/login')
    } catch (err: any) {
        console.error(err.message)
    }
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
