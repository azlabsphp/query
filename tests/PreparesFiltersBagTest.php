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

namespace Drewlabs\Query\Tests;

use Drewlabs\Query\PreparesFiltersBag;
use PHPUnit\Framework\TestCase;

class PreparesFiltersBagTest extends TestCase
{
    public function test_build_from_query_parameters()
    {
        $filters = PreparesFiltersBag::from_Query_Parameters(new Person(), new class() {
            private $inputs = [
                'lastname' => 'Azandrew',
                'age' => 29,
                'addresses__email' => 'azandrewdevelopper@gmail.com',
            ];
            use ViewModel;
        });
        $this->assertTrue('addresses' === $filters['exists'][0]['column']);
        $this->assertTrue(is_array($filters['exists'][0]['match']));
        $this->assertSame($filters['or'][0], ['lastname', 'like', '%Azandrew%']);
    }

    public function test_build_from_query_input()
    {
        $result = PreparesFiltersBag::from_Query_Body(new class() {
            private $inputs = [
                '_query' => [
                    'where' => ['age', 28],
                    'orWhere' => ['lastname', 'like', '%AZOMEDOH%'],
                    'whereHas' => [
                        'column' => 'addresses',
                        'match' => 'where(email, like, %azandrew@%)',
                    ],
                    'orderBy' => ['id'],
                ],
            ];
            use ViewModel;
        });
        $this->assertTrue('addresses' === $result['exists'][0]);
        $this->assertInstanceOf(\Closure::class, $result['exists'][1]);
        $this->assertSame($result['or'], ['lastname', 'like', '%AZOMEDOH%']);
    }

    public function test_build_method()
    {
        $result = PreparesFiltersBag::new(new class() {
            private $inputs = [
                'firstname' => 'SIDOINE',
                'age' => '20',
                '_query' => [
                    'orWhere' => ['lastname', 'like', '%AZOMEDOH%'],
                    'whereHas' => [
                        'column' => 'addresses',
                        'match' => [
                            'method' => 'where',
                            'params' => ['email', 'like', '%azandrew@%'],
                        ],
                    ],
                    'orderBy' => ['id'],
                ],
            ];
            use ViewModel;
        })->call(static function () {
            return new (Person::class);
        });
        $this->assertTrue(is_array($result['or']));
        $this->assertTrue(!\array_key_exists('and', $result));
    }

    public function test_filter_query_parameters_returns_and_clauses_if_value_contains_and_operator()
    {
        $result = PreparesFiltersBag::from_Query_Parameters(new Person(), $this->createParametersBag([
            'email' => '&&:==:azandrewdevelopper@gmail.com',
            'lastname' => 'and:=like:AZOMEDOH',
            'age' => '&&:>=:2022-10-10|&&:<=:2022-10-10',
        ]));
        $this->assertTrue(($result['and'] ?? null) !== null);
        $this->assertSame(['email', '=', 'azandrewdevelopper@gmail.com'], $result['and'][0]);
        $this->assertSame(['lastname', 'like', '%AZOMEDOH%'], $result['and'][1]);
    }

    public function test_build_query_filters_with_default_parameters()
    {
        $query = new class() {
            public function __invoke($query)
            {
                return $query->where('url', 'http://localhost:8000/pictures/1665418738634445f249513042648693');
            }
        };
        $result = PreparesFiltersBag::new(
            $this->createParametersBag(
                [
                    'email' => '&&:==:azandrewdevelopper@gmail.com',
                    'lastname' => 'and:=like:AZOMEDOH',
                    '_query' => [
                        'whereHas' => [
                            'column' => 'addresses',
                            'match' => 'and(email, like, %azandrew@%)'
                        ],
                        'orderBy' => ['id'],
                    ],
                ]
            )
        )->call(
            new Person(),
            [
                'whereHas' => ['profile', $query],
                'where' => ['age', 28],
            ]
        );
        $this->assertTrue(($result['exists'] ?? null) !== null);
        $this->assertSame(['profile', $query], $result['exists'][0]);
    }

    public function test_alternate_query_methods()
    {
        $result = PreparesFiltersBag::from_Query_Body(new class() {
            private $inputs = [
                '_query' => [
                    'where' => ['age', 28],
                    'or' => ['lastname', 'like', '%AZOMEDOH%'],
                    'exists' => [
                        'column' => 'addresses',
                        'match' => 'and(email, like, %azandrew@%)',
                    ],
                    'in' => ['likes', [10, 12, 2]],
                    'notin' => ['name', ['Milick', 'Jonh Doe']],
                    'notnull' => 'firstname',
                    'isnull' => 'lastname',
                    'sort' => ['id'],
                ],
            ];
            use ViewModel;
        });

        $this->assertTrue('addresses' === $result['exists'][0]);
        $this->assertInstanceOf(\Closure::class, $result['exists'][1]);
        $this->assertSame($result['or'], ['lastname', 'like', '%AZOMEDOH%']);
        $this->assertSame($result['in'], ['likes', [10, 12, 2]]);
        $this->assertSame($result['notIn'], ['name', ['Milick', 'Jonh Doe']]);
        $this->assertSame('firstname', $result['notNull']);
        $this->assertSame('lastname', $result['isNull']);
    }

    private function createParametersBag(array $inputs)
    {
        return new class($inputs) {
            /**
             * Parameters.
             *
             * @var array
             */
            private $values = [];

            /**
             * @return void
             */
            public function __construct(array $values)
            {
                $this->values = $values;
            }

            public function has(string $name)
            {
                return \in_array($name, array_keys($this->values), true);
            }

            public function get(string $name)
            {
                return $this->values[$name] ?? null;
            }

            public function all()
            {
                return $this->values;
            }
        };
    }
}
