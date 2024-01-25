<?php

namespace PauloHortelan\Onmt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\Onmt\Models\Ont;

class OntController extends Controller
{
    public function index(): JsonResponse
    {
        $onts = Ont::all();

        return response()->json($onts);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'interface' => 'required|string',
            'cto_id' => 'required|int',
        ]);

        $ont = Ont::create($validatedData);

        return response()->json($ont, 201);
    }

    public function show(Ont $ont): JsonResponse
    {
        return response()->json($ont);
    }

    public function update(Request $request, Ont $ont): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'interface' => 'required|string',
            'cto_id' => 'required|int',
        ]);

        $ont->update($validatedData);

        return response()->json($ont, 200);
    }

    public function destroy(Ont $ont): JsonResponse
    {
        $ont->delete();

        return response()->json(null, 204);
    }
}
