

<template>

<div class="flex items-center justify-center h-screen">
    <form v-if="!waitingOnVerification" class="w-full max-w-md" action="" @submit.prevent="handleLogin">
        <div class="flex flex-col space-y-2">
            <label for="phone" class="text-3xl font-semibold text-gray-700">Enter Your Phone Number</label>
            <input type="text" v-maska data-maska="#### ### ####" v-model="credentials.phone" id="phone" name="phone" placeholder="0902 234 6783" autocomplete="new-phone" required class="px-3 py-2 placeholder-gray-400 text-gray-700 relative bg-white rounded text-sm border border-gray-400 outline-none focus:outline-none focus:ring w-full"/>
            <button type="submit" @submit.prevent="handleLogin" class="bg-black text-white font-bold py-2 px-4 rounded">Continue</button>
        </div>
    </form>

    <form v-else class="w-full max-w-md" action="" @submit.prevent="handleVerification">
        <div class="flex flex-col space-y-2">
            <label for="login_code" class="text-3xl font-semibold text-gray-700">Enter Verification Code</label>
            <input type="text" v-maska data-maska="######" v-model="credentials.login_code" id="login_code" name="login_code" placeholder="123456" autocomplete="new-code" required class="px-3 py-2 placeholder-gray-400 text-gray-700 relative bg-white rounded text-sm border border-gray-400 outline-none focus:outline-none focus:ring w-full"/>
            <button type="submit" @submit.prevent="handleVerification" class="bg-black text-white font-bold py-2 px-4 rounded">Verify</button>
        </div>
    </form>
</div>

</template>

<script setup>

import { vMaska } from 'maska'
import { ref, reactive } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router';
import { onMounted } from 'vue'
import http from '../helpers/http';

const router = useRouter()

const credentials = reactive({
    phone: null
    
})

const waitingOnVerification = ref(false)

onMounted(() => {
    if (localStorage.getItem('token')) {
        router.push({ name: 'landing' })
    }
})

const getFormattedCredentials = () => {
    return {
        phone: credentials.phone.replaceAll(' ', ''),
        login_code: credentials.login_code
    }
}

const handleLogin = () => {

    axios.post('https://app.jojoelectricals.com/api/login', getFormattedCredentials())
    .then(response => {
        console.log(response.data)
        waitingOnVerification.value = true
    })
    .catch((error) => {
        console.error(error)
        alert(error.response.data.message)
    })
 
}

const handleVerification = () => {
    axios.post('https://app.jojoelectricals.com/api/login/verify', getFormattedCredentials())
    .then(response => {
        console.log(response.data) //auth token
        localStorage.setItem('token', response.data)
        router.push({ name: 'landing' })
    })
    .catch((error) => {
        console.error(error)
        alert(error.response.data.message)
    })
}

</script>