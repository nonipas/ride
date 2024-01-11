<template>
    <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">Here is your trip</h1>
        <form action="">
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left">
                <div class="px-4 py-5 bg-white sm:p-6">

                    <div >
                        <GMapMap :zoom="11" :center="location.destination.geometry" style="width: 100%; height: 256px;" ref="gMap">
                            
                        </GMapMap>
                       
                    </div>

                    <div class="mt-2">
                        <p class="text-xl">Going to <strong>{{ location.destination.name }}</strong></p>

                        <p class="text-xl">Amount: <strong>N {{ amount }} </strong></p>
                    
                    </div>
                    
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button @click.prevent="handleConfirmTrip" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Let's Go!
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<script setup>

import { useLocationStore } from '@/stores/location';

import { useRouter } from 'vue-router';

import { onMounted } from 'vue';

import { ref } from 'vue';

import axios from 'axios';

import { useTripStore } from '@/stores/trip';

import { http } from '@/helpers/http';

const location = useLocationStore();

const trip = useTripStore();

const router = useRouter();

const gMap = ref(null);

const amount = ref(0);


const handleConfirmTrip = () => {

    http().post('/api/trip', {

        origin: location.current.geometry,
        destination: location.destination.geometry,
        //get amount by multiplying the distance in km by 200
        amount: getAmount(
            location.current.geometry.lat,
            location.current.geometry.lng,
            location.destination.geometry.lat,
            location.destination.geometry.lng
        ),
        destination_name: location.destination.name,

    }).then((res) => {

        trip.$patch(res.data);
        router.push({ name: 'trip' });

    }).catch((err) => {

        console.log(err);

    });
}

const getAmount = (lat1, lon1, lat2, lon2) => {
    const R = 6371; // Radius of the Earth in kilometers
    const dLat = (lat2 - lat1) * (Math.PI / 180);
    const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c; // Distance in kilometers

    const amount = (Math.round(distance * 200 * 100) / 100).toFixed(0);

    return amount;
}

onMounted(async () => {

    if (location.destination.name === '') {
        router.push({ name: 'location' });
    }

    //get user current location
    await location.updateCurrentLocation();

    amount.value = getAmount(
        location.current.geometry.lat,
        location.current.geometry.lng,
        location.destination.geometry.lat,
        location.destination.geometry.lng
    );

    //draw a path on the map
    gMap.value.$mapPromise.then((mapObject) => {
        let currentPoint = new google.maps.LatLng(location.current.geometry),
            destinationPoint = new google.maps.LatLng(location.destination.geometry),
            directionService = new google.maps.DirectionsService(),
            directionDisplay = new google.maps.DirectionsRenderer(
                {
                    map: mapObject,
                }
            )
        
        directionService.route(
            {
                origin: currentPoint,
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

    

});

</script>