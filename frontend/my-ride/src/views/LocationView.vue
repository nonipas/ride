<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">Where are you going?</h1>
        <form action="">
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
                <div class="px-4 py-5 bg-white sm:p-6">
                        <div >
                            
                            <GMapAutocomplete @place_changed="handleLocationChanged" placeholder="My destination"  class="px-3 py-2 placeholder-gray-400 text-gray-700 relative bg-white rounded text-sm border border-gray-400 outline-none focus:outline-none focus:ring w-full" ></GMapAutocomplete>
                        </div>
                    
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button @click.prevent="handleSelectLocation" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Find A Ride
                    </button>
                </div>
            </div>
        </form>
    </div>

</template>

<script setup>

import { useLocationStore } from '@/stores/location';
import { useRouter } from 'vue-router';

const router = useRouter();

const location = useLocationStore();

const handleLocationChanged = (e) => {
    console.log('handleLocationChanged', e);
    location.$patch({
        destination: {
            name: e.name,
            address: e.formatted_address,
            geometry: {
                lat: e.geometry.location.lat(),
                lng: e.geometry.location.lng()}
        }
    });
}

const handleSelectLocation = () => {

    if (location.destination.name === '') {

        router.push({ name: 'map' });
        
    }
}

</script>