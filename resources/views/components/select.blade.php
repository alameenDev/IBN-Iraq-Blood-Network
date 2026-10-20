@props(['name','label','options'=>[],'value'=>null,'required'=>false,'placeholder'=>'اختر من القائمة'])
<label class="field"><span>{{ $label }} @if($required)<b class="required">*</b>@endif</span>
<select name="{{ $name }}" @required($required) {{ $attributes }}>
@if($placeholder!==null)<option value="">{{ $placeholder }}</option>@endif
@foreach($options as $key=>$text)<option value="{{ $key }}" @selected((string)old($name,$value)===(string)$key)>{{ $text }}</option>@endforeach
</select>@error($name)<small class="field-error">{{ $message }}</small>@enderror</label>
