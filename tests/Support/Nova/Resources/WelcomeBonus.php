<?php

namespace App\Nova\Resources;

use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\CreatedAtFilter;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\DateField;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\PublishPostAction;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\TextField;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\UnPublishPostAction;
use Laravel\Nova\Resource;

class WelcomeBonus extends Resource
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
            new TextField,
            new DateField,
        ];
    }

    public function cards(NovaRequest $request): array
    {
        return [];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new CreatedAtFilter,
        ];
    }

    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            new PublishPostAction,
            new UnPublishPostAction,
            new UnPublishPostAction,
        ];
    }
}
