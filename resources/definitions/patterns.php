<?php

use App\Infrastructure\Installers\PatternScaffoldInstaller;

$pattern = function (string $id, string $name, string $category, string $description = '', ?string $warning = null): array {
    return [
        'id' => $id,
        'name' => $name,
        'category' => $category,
        'description' => $description,
        'generator' => PatternScaffoldInstaller::class,
        'compatible_with' => ['service-layer', 'modular-monolith', 'ddd', 'mvc-service'],
        'warning' => $warning,
    ];
};

return [
    $pattern('factory', 'Factory Pattern', 'creational'),
    $pattern('abstract-factory', 'Abstract Factory', 'creational'),
    $pattern('builder', 'Builder Pattern', 'creational'),
    $pattern('prototype', 'Prototype Pattern', 'creational'),
    $pattern('singleton', 'Singleton Pattern', 'creational', '', "Laravel's service container is usually preferable. Singleton can introduce hidden global state."),
    $pattern('adapter', 'Adapter Pattern', 'structural'),
    $pattern('bridge', 'Bridge Pattern', 'structural'),
    $pattern('composite', 'Composite Pattern', 'structural'),
    $pattern('decorator', 'Decorator Pattern', 'structural'),
    $pattern('facade', 'Facade Pattern', 'structural'),
    $pattern('flyweight', 'Flyweight Pattern', 'structural'),
    $pattern('proxy', 'Proxy Pattern', 'structural'),
    $pattern('strategy', 'Strategy Pattern', 'behavioral'),
    $pattern('observer', 'Observer Pattern', 'behavioral'),
    $pattern('command', 'Command Pattern', 'behavioral'),
    $pattern('chain', 'Chain of Responsibility', 'behavioral'),
    $pattern('state', 'State Pattern', 'behavioral'),
    $pattern('template-method', 'Template Method', 'behavioral'),
    $pattern('mediator', 'Mediator Pattern', 'behavioral'),
    $pattern('memento', 'Memento Pattern', 'behavioral'),
    $pattern('iterator', 'Iterator Pattern', 'behavioral'),
    $pattern('visitor', 'Visitor Pattern', 'behavioral'),
    $pattern('interpreter', 'Interpreter Pattern', 'behavioral'),
    $pattern('specification', 'Specification Pattern', 'behavioral'),
    $pattern('service', 'Service Layer', 'laravel'),
    $pattern('repository', 'Repository Pattern', 'laravel'),
    $pattern('action', 'Action Pattern', 'laravel'),
    $pattern('dto', 'DTO', 'laravel'),
    $pattern('domain-service', 'Domain Service', 'laravel'),
    $pattern('value-object', 'Value Object', 'laravel'),
    $pattern('domain-event', 'Domain Event', 'laravel'),
    $pattern('aggregate', 'Aggregate', 'laravel'),
    $pattern('cqrs', 'CQRS', 'laravel'),
];
