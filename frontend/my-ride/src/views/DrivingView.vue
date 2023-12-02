<template>
 <div class="pt-16">
        <h1 class="text-3xl font-semibold mb-4">{{ title }}</h1>
        <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left" v-if="!trip.is_complete">
                <div class="px-4 py-5 bg-white sm:p-6">

                    <div >
                        <GMapMap :zoom="11" :center="location.current.geometry" style="width: 100%; height: 256px;" ref="gMap">

                            <GMapMarker :position="location.current.geometry" :icon="currentIcon" />
                            <GMapMarker :position="location.destination.geometry" :icon="destinationIcon" />
                            
                        </GMapMap>
                       
                    </div>
                    <div class="mt-2">
                        <p class="text-xl">Going to <strong>pick up a passenger</strong></p>
                    </div>
                    
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button v-if="trip.is_started" @click.prevent="handleEndTrip" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        End Trip
                    </button>
                    <button v-else @click.prevent="handleStartTrip" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Start Trip
                    </button>
                </div>
            </div>
            <div class="overflow-hidden shadow sm:rounded-md max-w-sm mx-auto text-left" v-else>
            <div class="px-4 py-5 bg-white sm:p-6">
                <!-- animation that lasts 2 secs -->


            </div>
        </div>
        </div>
        
</template>

<script setup>

import { ref, onMounted, onUnmounted } from 'vue';
import { useTripStore } from '@/stores/trip';
import { useLocationStore } from '@/stores/location';
import http from '../helpers/http';
import router from '../router';

const trip = useTripStore();
const location = useLocationStore();
const gMap = ref(null);
const intervalRef = ref(null);

const title = ref('Driving to passenger...')

const currentIcon = {
    url: 'src/assets/car.png',
    scaledSize: {
        width: 24,
        height: 24,
    },
    anchor: {
        x: 25,
        y: 25,
    },
};

const destinationIcon = {
    url: 'src/assets/destination.png',
    scaledSize: {
        width: 24,
        height: 24,
    },
    anchor: {
        x: 25,
        y: 25,
    },
};

const updateMapBounds = (mapObject) => {
    let originPoint = new google.maps.LatLng(location.current.geometry),
            destinationPoint = new google.maps.LatLng(location.destination.geometry),
            LatLngBounds = new google.maps.LatLngBounds();

        LatLngBounds.extend(originPoint)
        LatLngBounds.extend(destinationPoint)

        mapObject.fitBounds(LatLngBounds);
};

const broadcastDriverLocation = () => {
    http().post(`/api/trip/${trip.id}/location`, {
        driver_location: location.current,
    }).then((res) => {
    })
    .catch((err) => {
        console.log(err);
    })
};

const handleStartTrip = () => {

    http().post(`/api/trip/${trip.id}/start`)
    .then((res) => {
        title.value = 'Travelling to destination...'
        location.$patch({
            destination: {
                name: res.data.destination_name,
                geometry: res.data.destination
            }
        })

        trip.$patch(res.data);
    })
    .catch((err) => {
        console.log(err);
    })
}

const handleEndTrip = () => {

http().post(`/api/trip/${trip.id}/end`)
    .then((res) => {
    title.value = 'Trip completed!'

    trip.$patch(res.data);

    setTimeout(() => {
        trip.reset()
        location.reset()

        router.push({
            name: 'standby'
        })
    }, 3000)
})
.catch((err) => {
    console.log(err);
})
}

onMounted(() => {
    gMap.value.$mapPromise.then((mapObject) => {
        updateMapBounds(mapObject);

        intervalRef.value = setInterval(async () => {

            await location.updateCurrentLocation();

            //update driver current location in the database
            broadcastDriverLocation();

            updateMapBounds(mapObject);
        }, 5000);
    });
});

onUnmounted(() => {
    clearInterval(intervalRef.value);
    intervalRef.value = null;
});

</script>