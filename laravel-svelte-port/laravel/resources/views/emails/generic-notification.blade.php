{{ $content }}

@if(!empty($data))
Additional details:
@foreach($data as $key => $value)
- {{ $key }}: {{ is_scalar($value) ? $value : json_encode($value) }}
@endforeach
@endif
