<?php

namespace App\Nova;

use RonasIT\Support\Tests\Support\Command\Models\Post;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class PostResource extends Resource
{
    public static $model = Post::class;

    //TODO change field for the title if it required
    public static $title = 'name';

    //TODO change query fields if it required
    public static $search = ['id', 'name'];

    public static function label(): string
    {
        return 'Posts';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make('Id')
                ->required()
                ->sortable(),
            Text::make('Title')
                ->required()
                ->sortable(),
            Text::make('Created At')
                ->required()
                ->sortable(),
        ];
    }

    public function cards(Request $request): array
    {
        return [];
    }

    public function filters(Request $request): array
    {
        return [];
    }

    public function lenses(Request $request): array
    {
        return [];
    }

    public function actions(Request $request): array
    {
        return [];
    }
}
