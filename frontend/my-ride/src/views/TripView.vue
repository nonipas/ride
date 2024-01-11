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
                
                <div class="flex justify-between px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button v-if="paymentButton" @click.prevent="handlePayment" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Pay
                    </button>
                    <button v-if="verifyPaymentButton" @click.prevent="verifyPayment" type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-black hover:bg-black">
                        Verify Payment
                    </button>
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
import http, { host_name } from '../helpers/http';
import { api_url } from '../helpers/http';
import { useRouter } from 'vue-router';

const router = useRouter();

const trip = useTripStore();
const location = useLocationStore();
const gMap = ref(null);

const gMapObject = ref(null);

const paymentButton = ref(false);
const verifyPaymentButton = ref(false);


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

const handlePayment = () => {
    http().post(`/api/trip/${trip.id}/pay`, {
        amount: trip.amount * 100,
        payment_reference: ''+Math.floor((Math.random() * 1000000000) + 1),
        payment_method: 'card',
    }).then((res) => {
        console.log(res);

        verifyPaymentButton.value = true;
        paymentButton.value = false;
        //open authorization url in new tab
        window.open(res.data.authorization_url);
    })
    .catch((err) => {
        console.log(err);
    })
}

//verify payment
const verifyPayment = () => {
    http().post(`/api/trip/${trip.id}/verify`, {
        payment_reference: trip.payment_reference,
    }).then((res) => {
        console.log(res);
        if (res.data.payment_status == 'paid') {
            title.value = "Thank you for riding with us";
            message.value = `Your payment of ${res.data.amount} was successful`;

            setTimeout(() => {
                trip.reset();
                location.reset();

                router.push({
                    name: 'landing'
                })
            }, 5000)
        }else{
            title.value = "Payment failed";
            message.value = `Your payment of ${res.data.amount} was unsuccessful, please try again.`;
            verifyPaymentButton.value = false;
            paymentButton.value = true;
        }
    })
    .catch((err) => {
        console.log(err);
    })
}

//get driver info
const getDriverInfo = (id) => {
    http().get(`/api/driver/${id}`)
    .then((res) => {
        console.log(res);

        //out response
        return res.data;
        
    })
    .catch((err) => {
        console.log(err);
    })
}


onMounted(() => {

    gMap.value.$mapPromise.then((mapObject) => {
        gMapObject.value = mapObject;
    })

    //verify payment from url query
    if (router.currentRoute.value.query.reference) {
        
        verifyPayment();
    }


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

    

    echo.channel(`passenger_${trip.user_id}`)
        .listen('TripAccepted', (e) => {
            trip.$patch(e.trip);
            console.log('TripAccepted',e);
            location.$patch({
                current: {
                    geometry: e.trip.destination,
                }
            });
            const driver = getDriverInfo(e.trip.driver_id);
            console.log('Driver',driver);
            title.value = 'A driver is on the way';
            message.value = `${e.trip.driver.user.name} is coming in a ${e.trip.driver.year} ${e.trip.driver.color} ${e.trip.driver.make} ${e.trip.driver.model} with license plate ${e.trip.driver.license_number}`;
            // message.value = `Your driver is coming in a ${driver.year} ${driver.color} ${driver.make} ${driver.model} with license plate ${driver.license_number}`;
        
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

            const driver = getDriverInfo(e.trip.driver_id);

            console.log('Driver',driver);

            title.value = "You've arrived";
            message.value = `Hope you enjoyed your trip with ${e.trip.driver.user.name}`;
            // message.value = `Hope you enjoyed your trip with ${driver.user.name}`;

            paymentButton.value = true;
        })
        // .listen('TripPaid', (e) => {
        //     trip.$patch(e.trip);

        //     console.log('TripPaid', e);

        //     title.value = "Thank you for riding with us";
        //     message.value = `Your payment of ${e.trip.amount} was successful`;

        //     setTimeout(() => {
        //         trip.reset();
        //         location.reset();

        //         router.push({
        //             name: 'landing'
        //         })
        //     }, 5000)
        // })

});


</script>
