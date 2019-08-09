
@if ($withAuth)
Route::post('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@create'])->middleware('jwt.auth');
Route::put('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@update'])->middleware('jwt.auth');
Route::delete('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@delete'])->middleware('jwt.auth');
@else
Route::post('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@create']);
Route::put('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@update']);
Route::delete('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@delete']);
@endif
Route::get('/{{$entities}}/{id}', ['uses' => {{$entity}}Controller::class . '@get']);
Route::get('/{{$entities}}', ['uses' => {{$entity}}Controller::class . '@search']);
