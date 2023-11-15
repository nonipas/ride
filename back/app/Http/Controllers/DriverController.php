<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DriverController extends Controller
{

    public function show(Request $request)
    {
        //return the user with the driver relationship loaded
        $user = $request->user();
        $user->load('driver');

        return $user;
    }

    public function update(Request $request)
    {
        
        //validate the request
        $request->validate([
            'year' => 'required|numeric|between:2010,2024',
            'make' => 'required',
            'model' => 'required',
            'color' => 'required',
            'license_number' => 'required',
            'name' => 'required',
        ]);

        //update the user's name
        $user = $request->user();

        $user->update($request->only('name'));

        //create or update the driver associated with the user

        $user->driver()->updateOrCreate([], $request->only(
            'year',
            'make',
            'model',
            'color',
            'license_number',
        ));

        //return the user with the driver relationship loaded
        $user->load('driver');

        return $user;
    }
}
