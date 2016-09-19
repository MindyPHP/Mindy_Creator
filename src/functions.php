<?php

namespace Mindy\Creator;

function createObject()
{
    return call_user_func_array([Creator::class, 'createObject'], func_get_args());
}
