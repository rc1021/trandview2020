<?php

namespace App\Http\Repositories;
use App\Models\SignalHistory;

class SignalRepository
{

    public function doFire($clock, $request)
    {
        $signal = new SignalHistory;
        $signal->clock = $clock;
        $signal->message = $request->getContent();
        $signal->save();
    }

    public function getHistoryModel($request)
    {
        if($request->clock)
            return SignalHistory::where('clock', $request->clock)->orderBy('id', 'desc')->take(10)->get();
        return SignalHistory::orderBy('id', 'desc')->take(10)->get();
    }

}
