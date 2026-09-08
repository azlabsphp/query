<?php

declare(strict_types=1);

/*
 * This file is part of the drewlabs namespace.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Query\AST;

final class Builder
{
    /** @var string */
    private $pattern = '/(?:->|\b)(and|or|in|exists)(?=\()|\'[^\']*\'|"[^"]*"|([a-zA-Z0-9_\.]+)|([=><!]+)|([(),\[\]])/';

    /** @var int */
    private $index = 0;

    /** @var string[] */
    private $tokens = [];

    /** create a new AST tree builder instance */
    public function __construct() {}

    /**
     * parse string variable into numeric value if required
     * 
     * @param string $val
     * 
     * @return string|float|int 
     */
    private function parseVal(string $val)
    {
        $val = trim($val, "'\"");
        if (is_numeric($val)) {
            if (strlen($val) > 1 && $val[0] === '0' && $val[1] !== '.') {
                return $val;
            }
            return str_contains($val, '.') ? (float)$val : (int)$val;
        }
        return $val;
    }

    /**
     * @param null|string $expected 
     * @return ?string 
     * @throws \Exception 
     */
    private function consume(?string $expected = null)
    {
        $token = $this->tokens[$this->index] ?? null;
        if ($expected !== null && $token !== $expected) {
            throw new \Exception(sprintf("syntax error: expected '%s', got '%s' at index %d", $expected, $token, $this->index));
        }
        $this->index++;
        return $token;
    }


    /**
     * @return InExpression 
     */
    function buildIn()
    {
        ltrim($this->consume(), '->'); // read operator case on must appy or exists
        $this->consume('(');
        $field = $this->consume();
        $this->consume(',');
        $this->consume('[');
        $values = [];
        while ($this->index < count($this->tokens) && $this->tokens[$this->index] !== ']') {
            $val = trim($this->consume(), "'\"");
            array_push($values, $this->parseVal($val));
            if ($this->tokens[$this->index] === ',') {
                $this->consume(',');
            }
        }

        $this->consume(']');
        $this->consume(')');

        return new InExpression($field, $values);
    }


    /**
     * @return ExistsExpression
     */
    function buildExists()
    {
        $this->consume();
        // ltrim($method, '->'); // case we should check for orexists or exists
        $this->consume('(');
        $field = $this->consume();

        # read any trailing , if exists
        if (isset($this->tokens[$this->index]) && $this->tokens[$this->index] === ',') {
            $this->consume(',');
        }

        $subquery = [];
        # we should read a subquery statement
        if ($this->index < count($this->tokens) && $this->tokens[$this->index] !== ')') {
            while ($this->index < count($this->tokens) && $this->tokens[$this->index] !== ')') {
                $subquery[] = $this->walk();
                if (isset($this->tokens[$this->index]) && $this->tokens[$this->index] === ',') {
                    $this->consume(',');
                }
            }
        }

        if (isset($this->tokens[$this->index]) && $this->tokens[$this->index] === ')') {
            $this->consume(')');
        }

        return new ExistsExpression($field, $subquery);
    }

    /**
     * @return LogicalExpression 
     */
    function buildNested()
    {
        $operator = ltrim($this->consume(), '->');
        $this->consume('(');

        $expressions = [];
        while ($this->index < count($this->tokens) && $this->tokens[$this->index] !== ')') {
            $expressions[] = $this->walk();
            if (isset($this->tokens[$this->index]) && $this->tokens[$this->index] === ',') {
                $this->consume(',');
            }
        }
        $this->consume(')',);
        return new LogicalExpression(strtolower($operator), $expressions);
    }

    /**
     * traverse the list of tokens and build the AST tree
     * 
     * @return mixed 
     */
    private function walk()
    {
        $nodes = [];

        while ($this->index < count($this->tokens)) {
            $token = $this->tokens[$this->index];
            if ($token === ')') {
                break;
            }

            if (in_array($token, ['in', '->in'])) {
                $nodes[] = $this->buildIn();
            } else if (in_array($token, ['exists', '->exists'])) {
                array_push($nodes, $this->buildExists());
            } else if (in_array($token, ['and', 'or', '->or', '->and'])) {
                $nodes[] = $this->buildNested();
            } else {
                $field = $this->consume();
                $this->consume(',');
                $operator = $this->consume();

                if (isset($this->tokens[$this->index]) && $this->tokens[$this->index] === ',' && isset($this->tokens[$this->index + 1]) && $this->tokens[$this->index + 1] !== ')') {
                    $this->consume(',');
                    $val = $this->consume();
                } else {
                    $val = $operator;
                    $operator = '=';
                }

                $nodes[] = new Expression($field, $operator, $this->parseVal($val));
            }

            if (isset($this->tokens[$this->index]) && $this->tokens[$this->index] === ',') {
                $this->consume(',');
            }
        }

        return count($nodes) === 1 ? $nodes[0] : new ChainedExpression($nodes);
    }

    public function build(string $expression)
    {
        preg_match_all($this->pattern, $expression, $matches);
        $this->tokens = array_values(array_filter($matches[0], fn($v) => trim($v) !== ''));

        return $this->walk();
    }
}
