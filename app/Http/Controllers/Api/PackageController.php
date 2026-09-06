<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PackageController extends Controller
{
    // GET /api/packages — public, cached 1 hour
    public function index(): JsonResponse
    {
        $packages = Cache::remember('packages.all', 3600, function () {
            return Package::active()->get();
        });

        return response()->json([
            'success' => true,
            'data'    => PackageResource::collection($packages),
        ]);
    }

    // GET /api/packages/{slug}
    public function show(string $slug): JsonResponse
    {
        $package = Cache::remember("packages.{$slug}", 3600, function () use ($slug) {
            return Package::where('slug', $slug)->where('is_active', true)->first();
        });

        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Paket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new PackageResource($package),
        ]);
    }
}
