<?php

namespace PauloHortelan\Onmt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\Onmt\Models\Dio;

class DioController extends Controller
{
    public function index(): JsonResponse
    {
        $dios = Dio::all();

        return response()->json($dios);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'olt_id' => 'required|int',
        ]);

        $dio = Dio::create($validatedData);

        return response()->json($dio, 201);
    }

    public function show(Dio $dio): JsonResponse
    {
        return response()->json($dio);
    }

    public function update(Request $request, Dio $dio): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'olt_id' => 'required|int',
        ]);

        $dio->update($validatedData);

        return response()->json($dio, 200);
    }

    public function destroy(Dio $dio): JsonResponse
    {
        $dio->delete();

        return response()->json(null, 204);
    }
}
