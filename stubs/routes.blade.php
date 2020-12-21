@if (in_array('C', $options))
Route::post('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@create']);
@endif
@if (in_array('U', $options))
Route::put('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@update']);
@endif
@if (in_array('D', $options))
Route::delete('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@delete']);
@endif
@if (in_array('R', $options))
Route::get('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@get']);
Route::get('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@search']);
@endif