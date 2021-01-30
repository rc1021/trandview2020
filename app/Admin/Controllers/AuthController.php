<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use App\Http\Repositories\Admin\AuthKeySecretRepository;

class AuthController extends BaseAuthController
{
    public function getKeySecret(Content $content, AuthKeySecretRepository $rep)
    {
        return $rep->getKeySecret($content);
    }

    public function putKeySecret(Request $request, AuthKeySecretRepository $rep)
    {
        return $rep->putKeySecret($request);
    }
}
