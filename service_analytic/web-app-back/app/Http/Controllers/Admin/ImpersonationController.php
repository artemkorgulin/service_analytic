<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JWTImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

/**
 * ImpersonationController
 * Based on Lab404\Impersonate package
 */

class ImpersonationController extends Controller
{

    public function take(int $subjectId)
    {
        //todo: add check if current user allowed to impersonate
        $impersonationService = new JWTImpersonationService(app());

        $subject = User::findOrFail($subjectId);

        if ($impersonationService->take(auth()->user(), $subject, 'api_v1')) {
            $token = $impersonationService->getToken();

            return redirect(sprintf(
                    '%s/?%s',
                    config('env.front_app_url'),
                    http_build_query([
                        'is_admin'     => true,
                        'token'        => $token,
                        'redirect_url' => route('admin.impersonation.leave')
                    ]))
            );
        }

        return url()->previous();
    }


    public function leave()
    {
        $impersonationService = new JWTImpersonationService(app());
        if (!$impersonationService->isImpersonating()) {
            return redirect(config('nova.path').'/resources/users');
        }

        $subjectId = $impersonationService->getSubjectId();
        if ($impersonationService->leave()) {
            return $this->redirectToSubjectSourcePage($subjectId);
        }

        return redirect(config('nova.path'));
    }


    /**
     * @param  int|User  $subjectId
     *
     * @return RedirectResponse|Redirector
     */
    private function redirectToSubjectSourcePage(int|User $subjectId): RedirectResponse|Redirector {
        return redirect(sprintf('%s/resources/users/%d', config('nova.path'), $subjectId));
    }
}
