@php
    $results = blank($datasets) ? collect() : $datasets;
    $columns = blank($results) ? collect() : collect(($results[0])->getAttributes())->keys();
    $module = getCurrentModule();

    if ($columns->isNotEmpty()) {

        if ($columns->search('slug')) {
            $columns = $columns->except([$columns->search('slug')]);
        }

        $columns = $columns->prepend('name');
    }
@endphp

@if ($results->isEmpty())
    <div class="alert alert-error justify-normal">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>@lang('common.error.data_not_found')</span>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                @if ($columns->isNotEmpty())
                    <tr>
                        @foreach ($columns as $header)
                            <th class="bg-primary dark:text-base-300">
                                @lang('label.'.$header.'')
                            </th>
                        @endforeach
                    </tr>
                @endif
            </thead>
            <tbody>
                @foreach ($results as $result)
                    @if ($columns->isNotEmpty())
                        <tr class="hover">
                            @foreach ($columns as $column)
                                @if ($loop->first)
                                    <th>
                                        <a href="{{ route(''.$module.'.show', [''.$module.'' => $result->slug]) }}">
                                            {{ ucfirst($module) }} {{ $result[$column] }}
                                        </a>
                                    </th>
                                @else
                                    <td>
                                        {{ $result[$column] }}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            {{ $results->links() }}
        </div>
    </div>
@endif