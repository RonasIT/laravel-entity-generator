namespace App\Http\Controllers;

@inject('str', 'Illuminate\Support\Str')
use {{$resourcesNamespace}}\{{$str::plural($entity)}}CollectionResource;
@if (in_array('C', $options))
use {{$requestsNamespace}}\{{$requestsFolder}}\Create{{$entity}}Request;
@endif
@if (in_array('D', $options))
use {{$requestsNamespace}}\{{$requestsFolder}}\Delete{{$entity}}Request;
@endif
use {{$requestsNamespace}}\{{$requestsFolder}}\Get{{$entity}}Request;
@if (in_array('R', $options))
use {{$requestsNamespace}}\{{$requestsFolder}}\Search{{$str::plural($entity)}}Request;
@endif
@if (in_array('U', $options))
use {{$requestsNamespace}}\{{$requestsFolder}}\Update{{$entity}}Request;
@endif
use {{$resourcesNamespace}}\{{$entity}}Resource;
use {{$servicesNamespace}}\{{$entity}}Service;
@if (in_array('D', $options) || in_array('U', $options))
use Symfony\Component\HttpFoundation\Response;

@endif
class {{$entity}}Controller extends Controller
{
@if (in_array('C', $options))
    public function create(Create{{$entity}}Request $request, {{$entity}}Service $service)
    {
        $data = $request->onlyValidated();

        $result = $service->create($data);

        return {{$entity}}Resource::make($result);
    }

@endif
@if (in_array('R', $options))
    public function get(Get{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $result = $service
            ->with($request->input('with', []))
            ->withCount($request->input('with_count', []))
            ->find($id);

        return {{$entity}}Resource::make($result);
    }

    public function search(Search{{$str::plural($entity)}}Request $request, {{$entity}}Service $service)
    {
        $result = $service->search($request->onlyValidated());

        return {{$str::plural($entity)}}CollectionResource::make($result);
    }

@endif
@if (in_array('U', $options))
    public function update(Update{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $service->update($id, $request->onlyValidated());

        return response('', Response::HTTP_NO_CONTENT);
    }

@endif
@if (in_array('D', $options))
    public function delete(Delete{{$entity}}Request $request, {{$entity}}Service $service, $id)
    {
        $service->delete($id);

        return response('', Response::HTTP_NO_CONTENT);
    }
@endif
}