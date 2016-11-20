
<input id="field-{{ $key }}"
       type="{{ $type ?: 'text' }}"
       name="{{ $name ?: $key }}"
       value="{{ $value }}"
       class="form-control"
       @if ($required && ! $translated) required="required" @endif
       size="{{ array_get($options, 'size', array_get($options, 'maxlength')) }}"
       @if (array_get($options, 'maxlength'))
        maxlength="{{ array_get($options, 'maxlength') }}"
       @endif
       @if (null !== array_get($options, 'min')) min="{{ array_get($options, 'min') }}" @endif
       @if (null !== array_get($options, 'max')) max="{{ array_get($options, 'max') }}" @endif
       @if (array_get($options, 'step')) step="{{ array_get($options, 'step') }}" @endif
       @if (array_get($options, 'pattern')) pattern="{{ array_get($options, 'pattern') }}" @endif
>

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))


