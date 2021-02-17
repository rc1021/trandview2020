<?php

namespace App\Http\Repositories;
use App\Models\SignalHistory;

class SignalRepository
{

    public function doFire($clock, $request)
    {
        SignalHistory::parseAndPlay($clock, $request->getContent());
    }

    public function getHistoryModel($request)
    {
        if($request->clock)
            return SignalHistory::where('clock', $request->clock)->orderBy('id', 'desc')->take(10)->get();
        return SignalHistory::orderBy('id', 'desc')->take(10)->get();
    }

}
