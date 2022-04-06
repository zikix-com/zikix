<?php

namespace Zikix\Zikix;

use Exception;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{

    /**
     * @var string
     */
    public string $service = '';

    /**
     * @var string
     */
    public string $name = '';

    /**
     * @var string
     */
    public string $description = '';

    /**
     * @var bool
     */
    public bool $openapi = false;

    /**
     * @var bool
     */
    public bool $hidden = false;

    /**
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
