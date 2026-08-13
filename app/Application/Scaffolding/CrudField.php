<?php

namespace App\Application\Scaffolding;

final readonly class CrudField
{
    /**
     * @param  array<int, string>  $rules
     */
    public function __construct(
        public string $name,
        public string $type,
        public array $rules = [],
        public ?string $related = null,
    ) {}

    public static function parse(string $definition): self
    {
        $parts = explode('|', $definition);
        $nameType = array_shift($parts) ?? 'name:string';
        [$name, $type] = array_pad(explode(':', $nameType, 2), 2, 'string');

        $related = null;
        if ($type === 'foreign' || str_ends_with($name, '_id')) {
            $related = str_replace('_id', '', $name);
        }

        return new self($name, $type, $parts, $related);
    }

    public function input(): string
    {
        return match ($this->type) {
            'text' => 'textarea',
            'boolean' => 'checkbox',
            'integer' => 'number',
            'decimal' => 'currency',
            'date' => 'date',
            'datetime' => 'datetime',
            'enum' => 'select',
            'foreign' => 'relation',
            'file' => 'file',
            'image' => 'image',
            default => 'input',
        };
    }

    public function migrationType(): string
    {
        return match ($this->type) {
            'text' => 'text',
            'boolean' => 'boolean',
            'integer' => 'integer',
            'decimal' => 'decimal',
            'date' => 'date',
            'datetime' => 'dateTime',
            'foreign' => 'foreignId',
            'file', 'image' => 'string',
            default => 'string',
        };
    }

    public function isRequired(): bool
    {
        return in_array('required', $this->rules, true);
    }
}
