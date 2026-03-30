<?php

namespace App\Nova;

use Laravel\Nova\Http\Requests\NovaRequest;
use RonasIT\EntityGenerator\Tests\Support\NovaTestGeneratorTest\CreatedAtFilter;
use RonasIT\EntityGenerator\Tests\Support\NovaTestGeneratorTest\DateField;
use RonasIT\EntityGenerator\Tests\Support\NovaTestGeneratorTest\PublishPostAction;
use RonasIT\EntityGenerator\Tests\Support\NovaTestGeneratorTest\TextField;
use RonasIT\EntityGenerator\Tests\Support\NovaTestGeneratorTest\UnPublishPostAction;

class BaseTestResource
{
    public static $title = 'name';

    public static $search = ['id', 'name'];

    public static function label(): string
    {
        return 'WelcomeBonus';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            new TextField(),
            new DateField(),
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new CreatedAtFilter(),
        ];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            new PublishPostAction(),
            new UnPublishPostAction(),
            new UnPublishPostAction(),
        ];
    }
}
