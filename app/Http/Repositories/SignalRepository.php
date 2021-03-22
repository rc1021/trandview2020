<?php

namespace App\Http\Repositories;
use App\Models\SignalHistory;

class SignalRepository
{

    public function doFire($request)
    {
        SignalHistory::parseAndPlay($request->getContent());
    }

    public function getHistoryModel($request)
    {
        if($request->clock)
            return SignalHistory::where('clock', $request->clock)->orderBy('id', 'desc')->take(10)->get();
        return SignalHistory::orderBy('id', 'desc')->take(10)->get();
    }

}
