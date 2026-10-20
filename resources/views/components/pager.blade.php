@props(['items'])
<div class="pager"><span>عرض {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} من {{ $items->total() }}</span>
<nav aria-label="صفحات النتائج">@if($items->previousPageUrl())<a class="btn btn-light btn-sm" href="{{ $items->previousPageUrl() }}">السابق</a>@endif
<span>صفحة {{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
@if($items->nextPageUrl())<a class="btn btn-light btn-sm" href="{{ $items->nextPageUrl() }}">التالي</a>@endif</nav></div>
