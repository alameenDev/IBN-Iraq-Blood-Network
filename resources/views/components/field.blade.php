@props(['name','label','value'=>'','type'=>'text','required'=>false,'hint'=>null])
<label class="field"><span>{{ $label }} @if($required)<b class="required">*</b>@endif</span>
<input name="{{ $name }}" type="{{ $type }}" value="{{ $type==='password'?'':old($name,$value) }}" @required($required) {{ $attributes }}>
@if($hint)<small>{{ $hint }}</small>@endif
@error($name)<small class="field-error">{{ $message }}</small>@enderror
</label>
