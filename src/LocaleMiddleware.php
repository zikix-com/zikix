<?php

namespace Zikix\Zikix;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Class LocaleMiddleware
 */
class LocaleMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param null $guard
     *
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $language = $request->input('language');
        if (!$language) {
            // zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7
            $language = $request->header('Accept-Language');
            if (strpos($language, 'zh-CN') === 0) {
                $language = 'zh';
            }
            if (strpos($language, 'en-US') === 0) {
                $language = 'en';
            }
        }

        if (!$language) {
            $language = config('app.locale');
        }

        App::setLocale($language);

        return $next($request);
    }
}
