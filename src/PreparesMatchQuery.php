<?php


namespace Drewlabs\Query;

use Drewlabs\Query\Contracts\PreparesQuery;
use Drewlabs\Query\Exceptions\MalformedSubQueryException;
use TypeError;

class PreparesMatchQuery implements PreparesQuery
{
    /**
     * {@inheritDoc}
     * @param string|array $params 
     * @return array
     */
    public function __invoke($params)
    {
        if (is_array($params) && !empty($params)) {
            $method = !array_key_exists('method', $params) ? $params[key($params)] : $params['method'];
            $params = !array_key_exists('params', $params) ? array_values(array_slice($params, 1)) : $params['params'];
            return ['method' => $method, 'params' => $params];
        }
        if (!is_string($params)) {
            throw new TypeError('Expected method parameter to be an array or string, we got ' . (!is_null($params) && is_object($params) ? get_class($params) : gettype($params)));
        }
        // Case the parameters is not an array type, we parse the string in the format method(p1, p2, p3, ...)
        if (empty($method = $this->strBefore('(', $params))) {
            throw new MalformedSubQueryException($params);
        }
        $strParams = trim($this->strBefore(')', substr($params, strlen("$method("))));
        // Check if the query parameters is empty
        if (empty($strParams)) {
            throw new MalformedSubQueryException($params);
        }
        $queryParams = array_map(function ($p) {
            return trim($p);
        }, explode(',', $strParams));
        return ['method' => trim($method), 'params' => $queryParams];
    }

    /**
     * Query string before a given character
     * @param string $character 
     * @param string $haystack 
     * @return string 
     */
    private function strBefore(string $character, string $haystack)
    {
        if ($pos = strpos($haystack, $character)) {
            return substr($haystack, 0, $pos);
        }
        return '';
    }
}
