<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBandRequest;
use App\Jobs\RecordBandCreated;
use App\Models\Band;
use Illuminate\Http\JsonResponse;

class BandController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Band::query()->orderBy('id')->get());
    }

    public function store(StoreBandRequest $request): JsonResponse
    {
        $band = Band::query()->create($request->validated());

        RecordBandCreated::dispatch($band->id);

        return response()->json($band, 201)
            ->header('Location', route('bands.show', $band));
    }

    public function show(Band $band): JsonResponse
    {
        return response()->json($band);
    }

    public function byGender(string $gender): JsonResponse
    {
        $bands = Band::query()
            ->where('gender', mb_strtolower(trim($gender)))
            ->orderBy('id')
            ->get();

        return response()->json($bands);
    }
}
