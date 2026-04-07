<?php

namespace RonasIT\EntityGenerator\Enums;

enum ReservedFieldEnum: string
{
    case Id = 'id';
    case CreatedAt = 'created_at';
    case UpdatedAt = 'updated_at';

    public function cast(): string
    {
        return match ($this) {
            self::CreatedAt,
            self::UpdatedAt => 'datetime',
            self::Id => 'integer',
        };
    }

    public function annotation(): string
    {
        return match ($this) {
            self::CreatedAt,
            self::UpdatedAt => 'Carbon|null',
            self::Id => 'int',
        };
    }

    public function novaField(): array
    {
        return match ($this) {
            self::Id => [
                'type' => 'ID',
                'is_required' => false,
            ],
        };
    }

    public static function modelAutoFields(): array
    {
        return [
            self::CreatedAt,
            self::UpdatedAt,
        ];
    }

    public static function modelAutoAnnotations(): array
    {
        return [
            self::Id,
            ...self::modelAutoFields(),
        ];
    }

    public static function resourceAutoFields(): array
    {
        return [
            self::Id,
            ...self::modelAutoFields(),
        ];
    }

    public static function novaAutoFields(): array
    {
        return [self::Id];
    }
}
