<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request)
    {
        //validate the request
        $request->validate([
            'pickup_address' => 'required',
            'dropoff_address' => 'required',
            'requested_at' => 'required|date',
        ]);

        //create a trip for the user
        $trip = $request->user()->trips()->create($request->only(
            'pickup_address',
            'dropoff_address',
            'requested_at',
        ));

        //return the trip
        return $trip;
    }
}
