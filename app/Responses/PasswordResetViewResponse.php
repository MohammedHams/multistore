<?php

namespace App\Responses;

use Laravel\Fortify\Contracts\PasswordResetViewResponse as PasswordResetViewResponseContract;

class PasswordResetViewResponse implements PasswordResetViewResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }
}
