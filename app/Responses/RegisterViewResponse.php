<?php

namespace App\Responses;

use Laravel\Fortify\Contracts\RegisterViewResponse as RegisterViewResponseContract;

class RegisterViewResponse implements RegisterViewResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return view('auth.register');
    }
}
