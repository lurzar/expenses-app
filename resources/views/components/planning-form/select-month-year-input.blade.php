@props([
    'type' => null, 
    'values' => []
])

<select {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm'])  }}>
    @if ($type == 'month')
        <option selected disabled>Choose a {{ $type }}</option>
        @foreach ($values as $month)
            <option value="{{ $month }}">{{ $month }}</option>
        @endforeach
    @elseif ($type == 'year')
        <option selected disabled>Choose a {{ $type }}</option>
        @foreach ($values as $year)
            <option value="{{ $year }}">{{ $year }}</option>
        @endforeach
    @else
        <option selected disabled>Choose an option</option>
    @endif
</select>