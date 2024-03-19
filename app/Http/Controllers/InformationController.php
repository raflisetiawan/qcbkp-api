<?php

namespace App\Http\Controllers;

use App\Models\Information;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InformationController extends Controller
{
    /**
     * Display a listing of the information based on the specified date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get the start date and end date from the request parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Set the default date range to one week if not provided
        if (empty($startDate) && empty($endDate)) {
            $startDate = Carbon::now()->subDays(7)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } else {
            // Parse the start and end dates using Carbon
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        }

        // Retrieve information based on the specified date range
        $information = Information::whereBetween('information_date', [$startDate, $endDate])->get();

        return response()->json(['data' => $information]);
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
            'title' => 'required|string',
            'information' => 'required|string',
            'information_date' => 'required|date',
        ]);

        $information = Information::create($request->all());

        return response()->json(['data' => $information, 'message' => 'Information created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Information  $information
     * @return \Illuminate\Http\Response
     */
    public function show(Information $information)
    {
        return response()->json(['data' => $information]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Information  $information
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Information $information)
    {
        $request->validate([
            'title' => 'required|string',
            'information' => 'required|string',
            'information_date' => 'required|date',
        ]);

        $information->update($request->all());

        return response()->json(['data' => $information, 'message' => 'Information updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Information  $information
     * @return \Illuminate\Http\Response
     */
    public function destroy(Information $information)
    {
        $information->delete();

        return response()->json(['message' => 'Information deleted successfully']);
    }

    /**
     * Mengambil jumlah informasi .
     *
     * @return JsonResponse
     */
    public function getInformationsCount()
    {
        $informationsCount = Information::count();

        return response()->json([
            'success' => true,
            'informations_count' => $informationsCount,
        ], 200);
    }
}
