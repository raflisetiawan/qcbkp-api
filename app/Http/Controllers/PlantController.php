<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plants = Plant::all();

        return response()->json(['data' => $plants], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $plant = Plant::create($request->all());

        return response()->json(['data' => $plant], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Plant  $plant
     * @return \Illuminate\Http\Response
     */
    public function show(Plant $plant)
    {
        return response()->json(['data' => $plant], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Plant  $plant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Plant $plant)
    {
        $request->validate([
            'name' => 'string',
            'description' => 'nullable|string',
        ]);

        $plant->update($request->all());

        return response()->json(['data' => $plant], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Plant  $plant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Plant $plant)
    {
        $plant->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get all plants with only id and name.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllPlants()
    {
        $plants = Plant::select('id', 'name')->get();

        return response()->json(['data' => $plants], Response::HTTP_OK);
    }
}
