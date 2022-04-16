<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{

    /**
     * 所属服务或子系统名称
     *
     * @var string
     */
    public string $service = '';

    /**
     * API名称
     *
     * @var string
     */
    public string $name = '';

    /**
     * API 描述
     *
     * @var string
     */
    public string $description = '';

    /**
     * API 类型
     *
     * @var ApiType
     */
    public ApiType $apiType = ApiType::private;

    /**
     * 是否对外隐藏
     *
     * @var bool
     */
    public bool $hidden = false;

    /**
     * 参数属性的名称映射
     *
     * @var string[]
     */
    protected array $attributes = [];

    /**
     * @throws Exception
     */
    public function validate(): void
    {
        if ($this->rules() === []) {
            return;
        }

        $validator = Validator::make(
              Request::toArray(),
              $this->rules(),
              []
            , array_merge($this->attributes, $this->attributes())
        );

        if ($validator->fails()) {
            Api::badRequest(
                $validator->errors()->first()
            );
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }
}
