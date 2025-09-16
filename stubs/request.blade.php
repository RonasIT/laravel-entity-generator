@inject('requestsGenerator', 'RonasIT\Support\Generators\RequestsGenerator')
namespace {{ $namespace }}\{{ $requestsFolder }};

use {{ $namespace }}\Request;
@if($needToValidate)
use {{ $servicesNamespace }}\{{ $entity }}Service;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
@endif
@if($method === $requestsGenerator::SEARCH_METHOD)
use {{ $entityNamespace }};
@endif

class {{ $method }}{{ $entity }}Request extends Request
{
@if($method !== $requestsGenerator::DELETE_METHOD)
    public function rules(): array
    {
@if(!empty($parameters))
        return [
@foreach($parameters as $parameter)
    @if($method === $requestsGenerator::SEARCH_METHOD && $parameter['name'] === 'order_by')
        '{{ $parameter['name'] }}' => '{{ implode('|', $parameter['rules']) }}|in:' . self::getOrderableFields({{ Str::singular($entity) }}::class),
@continue;
    @endif
        '{{ $parameter['name'] }}' => '{{ implode('|', $parameter['rules']) }}',
@endforeach
        ];
@else
        return [];
@endif
    }
@endif
@if($method !== $requestsGenerator::DELETE_METHOD && $needToValidate)

@endif
@if($needToValidate)
    public function validateResolved(): void
    {
        parent::validateResolved();

        $service = app({{ $entity }}Service::class);

        if (!$service->exists($this->route('id'))) {
            throw new NotFoundHttpException(__('validation.exceptions.not_found', ['entity' => '{{ $entity }}']));
        }
    }
@endif
}