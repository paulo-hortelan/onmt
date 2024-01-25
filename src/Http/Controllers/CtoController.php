<?php

namespace PauloHortelan\Onmt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\Onmt\Models\Cto;

class CtoController extends Controller
{
    public function index(): JsonResponse
    {
        $ctos = Cto::all();

        return response()->json($ctos);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'ceo_splitter_id' => 'required|int',
        ]);

        $cto = Cto::create($validatedData);

        return response()->json($cto, 201);
    }

    public function show(Cto $cto): JsonResponse
    {
        return response()->json($cto);
    }

    public function update(Request $request, Cto $cto): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'ceo_splitter_id' => 'required|int',
        ]);

        $cto->update($validatedData);

        return response()->json($cto, 200);
    }

    public function destroy(Cto $cto): JsonResponse
    {
        $cto->delete();

        return response()->json(null, 204);
    }
}
