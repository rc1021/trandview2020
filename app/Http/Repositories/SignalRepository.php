<?php

namespace App\Http\Repositories;
use App\Models\SignalHistory;
use Illuminate\Support\Facades\Log;

class SignalRepository
{

    public function doFire($request)
    {
        SignalHistory::parseAndPlay($request->getContent(), 'margin');
    }

    public function getHistoryModel($request)
    {
        if($request->clock)
            return SignalHistory::where('clock', $request->clock)->orderBy('id', 'desc')->take(10)->get();
        return SignalHistory::orderBy('id', 'desc')->take(10)->get();
    }

    public function doFeatureFire($request)
    {
        Log::debug($request->getContent());
        // SignalHistory::parseAndPlay($request->getContent(), 'feature');
    }

}
