    public function {{ $name }}(): {{ Str::ucfirst($type) }}
    {
        return $this->{{ $type }}({{ $entity }}::class);
    }