<?php
declare(strict_types=1);

namespace App\Testing;

use App\Tests\HelloWorld;
use App\Tests\Crud;

final class TestRegistry
{
    public static function all(): array
    {
        return [
            'HelloWorld' => [
                'class' => HelloWorld::class,
                'description' => 'Kontrollerar att API:t returnerar någon sträng från GET /.',
                'demands' => [
                    "En endpoint direkt på /",
                    "returnerar en valfri sträng",
                    "returnerar HTTP 200",
                ]
            ],
            'Crud' => [
                'class' => Crud::class,
                'description' => 'Kontrollerar att API:t kan skapa, hämta, uppdatera och ta bort en användare.',
                'demands' => [
                    'POST /users tar emot JSON',
                    'POST /users kräver fältet name som en sträng',
                    'POST /users kräver fältet email som en sträng i giltigt e-postformat',
                    'POST /users returnerar HTTP 201',
                    'POST /users returnerar JSON med ett id',

                    'GET /users/{id} returnerar HTTP 200 för en befintlig användare',
                    'GET /users/{id} returnerar JSON',
                    'GET /users/{id} returnerar fälten id, name och email',
                    'GET /users/{id} returnerar samma id som returnerades vid skapandet',
                    'GET /users/{id} returnerar det name-värde som skickades vid skapandet',
                    'GET /users/{id} returnerar den email-adress som skickades vid skapandet',

                    'PUT /users/{id} tar emot JSON med name och email',
                    'PUT /users/{id} kan uppdatera användarens name',
                    'PUT /users/{id} returnerar HTTP 200 eller 204',
                    'Det uppdaterade name-värdet sparas',
                    'Den ursprungliga email-adressen finns kvar efter uppdateringen',

                    'DELETE /users/{id} tar bort användaren',
                    'DELETE /users/{id} returnerar HTTP 200 eller 204',
                    'GET /users/{id} efter DELETE returnerar HTTP 404',
                ],
            ],
        ];
    }

    public static function exists(string $name): bool
    {
        return isset(self::all()[$name]);
    }

    public static function get(string $name): string
    {
        $tests = self::all();

        if (!isset($tests[$name])) {
            throw new \InvalidArgumentException('Unknown test');
        }

        return $tests[$name]['class'];
    }
}