<?php
/**
 * NextPM - Open Source Project Management Script
 * Copyright (c) Muhammad Jaber. All Rights Reserved
 *
 * Email: mdjaber.swe@gmail.com
 *
 * LICENSE
 * --------
 * Licensed under the Apache License v2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace App\Exceptions;

use Exception;
use ErrorException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     *
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if (config('app.debug') == false) {
            // If the model is not found exception or not found HTTP exception and not ajax request.
            if ($e instanceof ModelNotFoundException
                || $e instanceof NotFoundHttpException
                && ! $request->ajax()
            ) {
                cache(['error_redirect' => 404], 300);

                return redirect()->route('404');
            } elseif ($e instanceof TokenMismatchException) {
                // Token mismatch exception.
                $error_message = 'Session has expired. Please refresh and try again.';

                if ($request->ajax()) {
                    return response()->json(['error' => 'TokenMismatch', 'message' => $error_message], 419);
                } else {
                    return redirect()->to(valid_app_url(url()->previous(), route('home')))->withDanger_message($error_message);
                }
            } elseif ($e instanceof ErrorException && ! $request->ajax()) {
                // Error exception and not ajax request.
                cache(['error_redirect' => 500], 300);

                return redirect()->route('500');
            } elseif (method_exists($e, 'getStatusCode')
                && in_array((int) $e->getStatusCode(), [404, 500])
                && ! $request->ajax()
            ) {
                cache(['error_redirect' => $e->getStatusCode()], 300);

                return redirect()->route((string) $e->getStatusCode());
            }
        }

        return parent::render($request, $e);
    }
}
