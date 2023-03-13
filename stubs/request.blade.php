@inject('requestsGenerator', 'RonasIT\Support\Generators\RequestsGenerator')
namespace App\Http\Requests\{{$requestsFolder}};

@if($needToValidate)
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\{{$entity}}Service;
@endif
use App\Http\Requests\Request;

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
@if($method !== $requestsGenerator::DELETE_METHOD)

@endif
@if(app()::VERSION < 5.6)
    public function validate()
    {
        parent::validate();

@else
    public function validateResolved()
    {
        parent::validateResolved();

@endif
        $service = app({{$entity}}Service::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => '{{$entity}}']));
        }
    }
@endif
}