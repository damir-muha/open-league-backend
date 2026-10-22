<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AdvertisementCollection;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Возвращаем только активные рекламные блоки, как и планировалось
        return new AdvertisementCollection(Advertisement::where('is_active', true)->get());
    }
}
