@inject('requestsGenerator', 'RonasIT\Support\Generators\RequestsGenerator')
namespace {{$namespace}}\{{$requestsFolder}};

use {{$namespace}}\Request;
@if($needToValidate)
use {{$servicesNamespace}}\{{$entity}}Service;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
@endif

class {{$method}}{{$entity}}Request extends Request
{
@if($method !== $requestsGenerator::DELETE_METHOD)
    public function rules(): array
    {
@if(!empty($parameters))
        return [
@foreach($parameters as $parameter)
            '{{$parameter['name']}}' => '{{implode('|', $parameter['rules'])}}',
@endforeach
        ];
@else
        return [];
@endif
    }
@endif
@if($needToValidate)
    public function validateResolved(): void
    {
        parent::validateResolved();

        $service = app({{$entity}}Service::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => '{{$entity}}']));
        }
    }
@endif
}