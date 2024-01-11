<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Events\TripCreated;
use App\Events\TripPaid;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request)
    {
        //validate the request
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required',
        ]);

        //create a trip for the user
        $trip = $request->user()->trips()->create($request->only(
            'origin',
            'destination',
            'destination_name',
            'amount'
        ));

        TripCreated::dispatch($trip, $request->user());

        //return the trip
        return $trip;
    }

    public function show(Request $request, Trip $trip)
    {
        //get the trip
        // $trip = $request->user()->trips()->findOrFail($trip);

        if ($trip->user->id === $request->user()->id) {
            return $trip;
        }

        //is the user the driver?

        if($trip->driver && $request->user()->driver){
            if($trip->driver->id === $request->user()->driver->id){
                return $trip;
            }
        }

        //return message
        return response()->json([
            'message' => 'Cannot find this trip.'
        ], 404);

    }

    public function accept(Request $request, Trip $trip)
    {
        //a driver accepts a trip

        $request->validate([
           'driver_location' => 'required',
        ]);

        //get driver id from the request

        $driver_id = $request->user()->driver->id;

        $trip->update([
            'driver_id' => $driver_id,
            'driver_location' => $request->driver_location,
        ]);

        //broadcast trip accepted event

        //load the driver and user
        $trip->load('driver.user');

        
        TripAccepted::dispatch($trip,$trip->user);

        return $trip;
    }   

    public function start(Request $request, Trip $trip)
    {
        $trip->update([
            'is_started' => true,
        ]);

        $trip->load('driver.user');

        TripStarted::dispatch($trip,$trip->user);

        return $trip;

    }

    public function end(Request $request, Trip $trip)
    {
        $trip->update([
            'is_complete' => true,
        ]);

        $trip->load('driver.user');

        TripEnded::dispatch($trip,$trip->user);

        return $trip;
    }

    public function location(Request $request, Trip $trip)
    {
        $request->validate([
            'driver_location' => 'required',
        ]);

        $trip->update([
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');

        TripLocationUpdated::dispatch($trip,$trip->user);

        return $trip;
    }


    public function pay(Request $request, Trip $trip)
    {
       //paystack to handle payment
         $request->validate([              
              'payment_reference' => 'required',
         ]);

         $url = "https://api.paystack.co/transaction/initialize";

        $fields = [
            'email' => 'chinonsopascal@gmail.com',
            'amount' => $trip->amount * 100,
            'reference' => $request->payment_reference,

        ];

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer sk_test_134f686e4a29af66091c2d8f5f2c7aab98e6f957",
            "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);

        //update the trip
        $trip->update([
            'payment_status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference,
        ]);

        $result = json_decode($result);

        return $result->data;

    }

    //verify payment

    public function verify(Request $request, Trip $trip)
    {

        $url = "https://api.paystack.co/transaction/verify/" . $trip->payment_reference;

        //open connection
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer sk_test_134f686e4a29af66091c2d8f5f2c7aab98e6f957",
            "Cache-Control: no-cache",
        ));
        
        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
        
        //execute post
        $result = curl_exec($ch);

        $data = json_decode($result);

        if($data->data->status === 'success'){
            $trip->update([
                'payment_status' => 'paid',
                'payment_method' => 'card',
            ]);

            //broadcast trip paid event
            $trip->load('driver.user');

            TripPaid::dispatch($trip,$trip->user);
        }

        return $trip;
    }

    
}
