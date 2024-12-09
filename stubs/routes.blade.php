Route::controller({{$entity}}Controller::class)->group(function () {
@if (in_array('C', $options))
    Route::post('{{$entities}}', 'create');
@endif
@if (in_array('U', $options))
    Route::put('{{$entities}}/{id}', 'update');
@endif
@if (in_array('D', $options))
    Route::delete('{{$entities}}/{id}', 'delete');
@endif
@if (in_array('R', $options))
    Route::get('{{$entities}}/{id}', 'get');
    Route::get('{{$entities}}', 'search');
@endif
});