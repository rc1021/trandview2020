<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Repositories\SignalRepository;

class SignalController extends Controller
{
    protected $m_rep;

    public function __construct(SignalRepository $rep)
    {
        $this->m_rep = $rep;
    }

    public function fire(Request $request)
    {
        $this->m_rep->doFire($request);
    }
}
