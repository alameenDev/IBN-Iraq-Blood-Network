@props(['value'])
<span class="badge badge-{{ $value }}"><span class="status-dot" aria-hidden="true"></span>{{ \App\Support\Catalog::label($value) }}</span>
