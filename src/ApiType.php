<?php

namespace Zikix\Zikix;

enum ApiType: int
{
    case private = 0;
    case open = 1;
    case inner = 2;
}
