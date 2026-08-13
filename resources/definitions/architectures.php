<?php

return [
    ['id' => 'mvc', 'name' => 'Standard Laravel MVC', 'description' => 'Default Laravel structure.', 'advanced' => false],
    ['id' => 'mvc-service', 'name' => 'MVC + Service Layer', 'description' => 'Thin controllers with app/Services.', 'implied_features' => ['service-layer'], 'implied_patterns' => ['service']],
    ['id' => 'repository', 'name' => 'Repository Pattern', 'description' => 'Persistence behind repositories.', 'implied_features' => ['service-layer', 'repository-basic'], 'implied_patterns' => ['service', 'repository']],
    ['id' => 'modular-monolith', 'name' => 'Modular Monolith', 'description' => 'Modules with explicit boundaries.', 'implied_features' => ['service-layer'], 'implied_patterns' => ['service']],
    ['id' => 'ddd', 'name' => 'Domain Driven Design', 'description' => 'Domain, application, infrastructure, presentation.', 'advanced' => true, 'implied_features' => ['service-layer', 'repository-domain'], 'implied_patterns' => ['service', 'repository', 'dto']],
    ['id' => 'clean', 'name' => 'Clean Architecture', 'description' => 'Dependency rule toward the domain.', 'advanced' => true, 'implied_features' => ['service-layer'], 'implied_patterns' => ['service']],
    ['id' => 'hexagonal', 'name' => 'Hexagonal Architecture', 'description' => 'Ports and adapters.', 'advanced' => true, 'implied_features' => ['service-layer'], 'implied_patterns' => ['adapter']],
    ['id' => 'onion', 'name' => 'Onion Architecture', 'description' => 'Domain at the center.', 'advanced' => true, 'implied_features' => ['service-layer']],
    ['id' => 'cqrs', 'name' => 'CQRS', 'description' => 'Commands and queries separated.', 'advanced' => true, 'implied_features' => ['cqrs']],
    ['id' => 'event-driven', 'name' => 'Event Driven', 'description' => 'Domain events and listeners.', 'advanced' => true, 'implied_features' => ['event-driven']],
    ['id' => 'microservice-ready', 'name' => 'Microservice Ready', 'description' => 'Service boundaries without multi-deploy.', 'advanced' => true],
    ['id' => 'multi-tenant', 'name' => 'Multi-Tenant', 'description' => 'Tenant isolation scaffolding.', 'implied_features' => ['tenancy-shared']],
    ['id' => 'custom', 'name' => 'Custom', 'description' => 'Start from MVC and opt in.'],
];
