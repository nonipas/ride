<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\User;

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

    //get driver info by id
    public function getDriverInfo(Request $request, $id)
    {
        $driver = Driver::find($id);
        $driver_name = User::find($driver->user_id)->name;

        //create an array to store the driver info
        $driver_info = array(
            'name' => $driver_name,
            'year' => $driver->year,
            'make' => $driver->make,
            'model' => $driver->model,
            'color' => $driver->color,
            'license_number' => $driver->license_number,
        );

        //return the driver info

        return $driver_info;
    }

}
