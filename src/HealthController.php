<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class HealthController extends BaseController
{
    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function action()
    {
        return Api::ok([], 'OK');
    }
}
