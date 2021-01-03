<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\SignalRepository;

class SignalHistoryController extends Controller
{
    protected $m_rep;

    public function __construct(SignalRepository $rep)
    {
        $this->m_rep = $rep;
    }

    public function __invoke(Request $request)
    {
        $collection = $this->m_rep->getHistoryModel($request);
        return view('signalhistory.index', compact('collection'));
    }
}
