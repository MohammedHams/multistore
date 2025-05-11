<?php

namespace App\Responses;

use Laravel\Fortify\Contracts\VerifyEmailViewResponse as VerifyEmailViewResponseContract;

class VerifyEmailViewResponse implements VerifyEmailViewResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return view('auth.verify-email');
    }
}
