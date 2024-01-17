<?php

namespace PauloHortelan\OltMonitoring\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\OltMonitoring\Models\Olt;

class OltController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $olts = Olt::all();

        return response()->json($olts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
        ]);

        $olt = Olt::create($validatedData);

        return response()->json($olt, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Olt $olt): JsonResponse
    {
        return response()->json($olt);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Olt $olt): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
        ]);

        $olt->update($validatedData);

        return response()->json($olt, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Olt $olt): JsonResponse
    {
        $olt->delete();

        return response()->json(null, 204);
    }
}
