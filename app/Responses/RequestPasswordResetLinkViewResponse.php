<?php

namespace App\Responses;

use Laravel\Fortify\Contracts\RequestPasswordResetLinkViewResponse as RequestPasswordResetLinkViewResponseContract;

class RequestPasswordResetLinkViewResponse implements RequestPasswordResetLinkViewResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return view('auth.forgot-password');
    }
}
