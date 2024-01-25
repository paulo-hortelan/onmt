<?php

namespace PauloHortelan\Onmt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\Onmt\Models\Ceo;

class CeoController extends Controller
{
    public function index(): JsonResponse
    {
        $ceos = Ceo::all();

        return response()->json($ceos);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'dio_id' => 'required|int',
        ]);

        $ceo = Ceo::create($validatedData);

        return response()->json($ceo, 201);
    }

    public function show(Ceo $ceo): JsonResponse
    {
        return response()->json($ceo);
    }

    public function update(Request $request, Ceo $ceo): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'dio_id' => 'required|int',
        ]);

        $ceo->update($validatedData);

        return response()->json($ceo, 200);
    }

    public function destroy(Ceo $ceo): JsonResponse
    {
        $ceo->delete();

        return response()->json(null, 204);
    }
}
