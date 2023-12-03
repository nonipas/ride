<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">{{ title }}</h1>
        <form action="">
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
                <div class="px-4 py-5 bg-white sm:p-6">

                    <div >
                        <GMapMap :zoom="14" :center="location.current.geometry" style="width: 100%; height: 256px;" ref="gMap">
                                
                            <GMapMarker :position="location.current.geometry" :icon="currentIcon" />
                            <GMapMarker v-if="trip.driver_location.geometry" :position="trip.driver_location.geometry" :icon="driverIcon" />
                            
                        </GMapMap>
                       
                    </div>
                    
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <span>{{ message }}</span>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { ref, onMounted, onUnmounted } from 'vue';
import { useTripStore } from '@/stores/trip';
import { useLocationStore } from '@/stores/location';
import http, { host_url } from '../helpers/http';
import { api_url } from '../helpers/http';
import { useRouter } from 'vue-router';

const router = useRouter();

const trip = useTripStore();
const location = useLocationStore();
const gMap = ref(null);

const gMapObject = ref(null)

const title = ref('waiting on a driver...');
const message = ref('When a driver accepts the trip, their info will appear here');

const currentIcon = {
    url: 'src/assets/destination.png',
    scaledSize: {
        width: 32,
        height: 32,
    },
    anchor: {
        x: 25,
        y: 25,
    },
};

const driverIcon = {
    url: 'src/assets/car.png',
    scaledSize: {
        width: 32,
        height: 32,
    },
    anchor: {
        x: 25,
        y: 25,
    },
};

const updateMapBounds = () => {
    let originPoint = new google.maps.LatLng(location.current.geometry),
        driverPoint = new google.maps.LatLng(trip.driver_location.geometry),
        LatLngBounds = new google.maps.LatLngBounds();

        LatLngBounds.extend(originPoint)
        LatLngBounds.extend(driverPoint)

        gMapObject.value.fitBounds(LatLngBounds);
};

onMounted(() => {

    gMap.value.$mapPromise.then((mapObject) => {
        gMapObject.value = mapObject;
    })

    let echo = new Echo({
        broadcaster: 'pusher',
        key: 'mykey',
        cluster: 'mt1',
        wsHost: host_url,
        wsPort: 6001,
        wssPort: 6001,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
    });

    

    echo.channel(`passenger_${trip.user_id}`)
        .listen('TripAccepted', (e) => {
            trip.$patch(e.trip);
            location.$patch({
                current: {
                    geometry: e.trip.destination,
                }
            });

            title.value = 'A driver is on the way';
            message.value = `${e.trip.driver.user.name} is coming in a ${e.trip.driver.year} ${e.trip.driver.color} ${e.trip.driver.make} ${e.trip.driver.model} with license plate ${e.trip.driver.license_number}`;
        
        })
        .listen('TripLocationUpdated', (e) => {
            
            trip.$patch(e.trip);

            setTimeout(updateMapBounds, 1000)

        })
        .listen('TripStarted', (e) => {
            trip.$patch(e.trip);

            title.value = 'You are on your way';
            message.value = `You are headed to ${e.trip.destination_name}`;
        })
        .listen('TripEnded', (e) => {
            trip.$patch(e.trip);

            title.value = "You've arrived";
            message.value = `Hope you enjoyed your trip with ${e.trip.driver.user.name}`;

            setTimeout(() => {
                trip.reset();

                location.reset();

                router.push({
                    name: 'landing'
                })
            }, 10000);
        })


});



</script>