<?php

namespace PauloHortelan\Onmt\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PauloHortelan\Onmt\Models\CeoSplitter;

class CeoSplitterController extends Controller
{
    public function index(): JsonResponse
    {
        $ceoSplitters = CeoSplitter::all();

        return response()->json($ceoSplitters);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'slot' => 'required|int',
            'pon' => 'required|int',
            'ceo_id' => 'required|int',
        ]);

        $ceoSplitter = CeoSplitter::create($validatedData);

        return response()->json($ceoSplitter, 201);
    }

    public function show(CeoSplitter $ceoSplitter): JsonResponse
    {
        return response()->json($ceoSplitter);
    }

    public function update(Request $request, CeoSplitter $ceoSplitter): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'slot' => 'required|int',
            'pon' => 'required|int',            
            'ceo_id' => 'required|int',
        ]);

        $ceoSplitter->update($validatedData);

        return response()->json($ceoSplitter, 200);
    }

    public function destroy(CeoSplitter $ceoSplitter): JsonResponse
    {
        $ceoSplitter->delete();

        return response()->json(null, 204);
    }
}
