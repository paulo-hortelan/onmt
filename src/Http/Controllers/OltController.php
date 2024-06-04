<?php

namespace PauloHortelan\Onmt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\Onmt\Models\Olt;

class OltController extends Controller
{
    public function index(): JsonResponse
    {
        $olts = Olt::all();

        return response()->json($olts);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'host_server' => 'required|string',
            'host_connection' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
        ]);

        $olt = Olt::create($validatedData);

        return response()->json($olt, 201);
    }

    public function show(Olt $olt): JsonResponse
    {
        return response()->json($olt);
    }

    public function update(Request $request, Olt $olt): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'host_connection' => 'required|string',
            'host_server' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'brand' => 'required|string',
            'model' => 'required|string',
        ]);

        $olt->update($validatedData);

        return response()->json($olt, 200);
    }

    public function destroy(Olt $olt): JsonResponse
    {
        $olt->delete();

        return response()->json(null, 204);
    }
}
