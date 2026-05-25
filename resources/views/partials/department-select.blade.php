@php
    $selected = old('department', $selected ?? '');
    $selected = \App\Support\Department::normalize($selected) ?? $selected;
@endphp

<select name="department" {{ ($required ?? true) ? 'required' : '' }} class="input" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
    <option value="" disabled {{ $selected === '' || $selected === null ? 'selected' : '' }}>Select a department</option>
    @foreach(\App\Support\Department::OPTIONS as $code => $label)
        <option value="{{ $code }}" {{ $selected === $code ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>
