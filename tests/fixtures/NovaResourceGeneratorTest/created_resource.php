<?php

namespace App\Nova;

use App\Models\Post;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\ID;

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
            Boolean::make('Is Published')
                ->sortable(),
            Text::make('Title')
                ->required()
                ->sortable(),
            Text::make('Body')
                ->required()
                ->sortable(),
            ID::make('Id')
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
