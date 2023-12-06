<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">{{ title }}</h1>
        <div v-if="!trip.id" class="mt-8 flex justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
        <div v-else>
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
                <div class="px-4 py-5 bg-white sm:p-6">

                    <div >
                        <GMapMap :zoom="11" :center="trip.destination" style="width: 100%; height: 256px;" ref="gMap">
                            
                        </GMapMap>
                    
                    </div>

                    <div class="mt-2">
                        <p class="text-xl">Going to <strong>{{ trip.destination_name }}</strong></p>
                    </div>

                </div>
                <div class="flex justify-between px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button @click.prevent="handleDeclineTrip" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Decline
                    </button>
                    <button @click.prevent="handleAcceptTrip" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Accept
                    </button>
                    
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>

import { onMounted } from 'vue';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { http } from '@/helpers/http';
import { useRouter } from 'vue-router';
import { useTripStore } from '@/stores/trip';
import { useLocationStore } from '@/stores/location';
import { ref } from 'vue';
import { api_url, host_name } from '../helpers/http';

const router = useRouter();
const title = ref('waiting for ride request...')
const trip = useTripStore();
const location = useLocationStore();
const gMap = ref(null);

const handleAcceptTrip = () => {
    http().post(`/api/trip/${trip.id}/accept`, {
        driver_location: location.current,
    }).then((res) => {
        console.log(res);
        location.$patch({
            destination: {
                name: 'Passenger',
                geometry: res.data.origin,
            }
        })
        router.push({ name: 'driving' });
    })
    .catch((err) => {
        console.log(err);
    })
}

const handleDeclineTrip = () => {
    trip.reset();
    title.value = 'waiting for ride request...';
    
}

onMounted(async () => {

    await location.updateCurrentLocation();
    
    let echo = new Echo({
        broadcaster: 'pusher',
        key: 'mykey',
        cluster: 'mt1',
        wsHost: host_name,
        wsPort: 6001,
        wssPort: 6001,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
    });

    

    echo.channel('drivers')
        .listen('TripCreated', (e) => {
            title.value = 'Ride requested:';
            trip.$patch(e.trip);
            console.log('TripCreated',e);
            setTimeout(() => {
                initMapDirections();
            }, 2000);
        })

    

})

const initMapDirections = () => {
        gMap.value.$mapPromise.then((mapObject) => {

            let originPoint = new google.maps.LatLng(trip.origin),
                destinationPoint = new google.maps.LatLng(trip.destination),
                directionService = new google.maps.DirectionsService(),
                directionDisplay = new google.maps.DirectionsRenderer(
                    {
                        map: mapObject,
                    }
                )
            
            directionService.route(
                {
                    origin: originPoint,
                    destination: destinationPoint,
                    avoidTolls: false,
                    avoidHighways: false,
                    travelMode: google.maps.TravelMode.DRIVING
                },
                (res, status) => {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionDisplay.setDirections(res);
                    } else {
                        console.log('Directions request failed due to ' + status);
                    }
                }
            );

        });
    }

</script>