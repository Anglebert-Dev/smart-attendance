<select name="period_id" class="input sm:w-auto">
    <option value="">All Periods</option>
    @foreach($periods as $period)
        <option value="{{ $period->id }}" {{ (string) request('period_id') === (string) $period->id ? 'selected' : '' }}>
            {{ $period->name }} ({{ $period->timeRangeLabel() }})
        </option>
    @endforeach
</select>
