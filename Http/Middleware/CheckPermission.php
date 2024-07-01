<?php

namespace Modules\ItkPortalSubmit\Http\Middleware;

use Closure;

class CheckPermission
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    // TODO: Add your logic here.

    if ($request->survey_submitted == true) {
      return redirect('home');
    }

    return $next($request);
  }
}