<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">Driver and Car details</h1>
        <form action="" @submit.prevent="handleSaveDriver">
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
                <div class="px-4 py-5 bg-white sm:p-6">

                    <div >
                       <input type="text" name="name" id="name" v-model="driverDetails.name" placeholder="Full name" class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm">
                    </div>

                    <div class="mt-2">
                        <input type="number" name="year" id="year" v-model="driverDetails.year" placeholder="Car Year" class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm">
                    </div>

                    <div class="mt-2">
                        <input type="text" name="make" id="make" v-model="driverDetails.make" placeholder="Car make" class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm">
                    </div>

                    <div class="mt-2">
                        <input type="text" name="model" id="model" v-model="driverDetails.model" placeholder="Car Model" class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm">
                    </div>

                    <div class="mt-2">
                        <input type="text" name="color" id="color" v-model="driverDetails.color" placeholder="Color" class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm">
                    </div>

                    <div class="mt-2">
                        <input type="text" name="license_number" v-model="driverDetails.license_number" id="licence_plate" placeholder="Licence Plate Number" class="mt-1 block w-full px-3 py-2 rounded-md border border-gray-300 shadow-sm">
                    </div>
                    
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button @click.prevent="handleSaveDriver" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                       Continue
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>

import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import { http } from '@/helpers/http';

const router = useRouter();

const driverDetails = reactive({
    name: '',
    year: null,
    make: '',
    model: '',
    color: '',
    license_number: '',
})

const handleSaveDriver = () => {

    http().post('/api/driver', driverDetails)

    .then((res) => {

        console.log(res);
        router.push({ name: 'standby' })

    }).catch((err) => {

        console.log(err);

    })
}

</script>