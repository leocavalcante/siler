<?php
/**
 * Helpers functions for HTTP requests
 */

namespace Siler\Http\Request;

function raw($input = 'php://input')
{
    return file_get_contents($input);
}

function params($input = 'php://input')
{
    $params = [];
    parse_str(raw($input), $params);
    return $params;
}

function json($input = 'php://input')
{
    return json_decode(raw($input), true);
}
