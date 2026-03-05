@props(['url', 'logo' => null])

<tr>
<td class="header">
    <a href="{{ $url }}" style="display:inline-block;">
        @if ($logo)
            <img src="{{ $logo }}" alt="Logo" width="150">
        @else
            {{ config('app.name') }}
        @endif
    </a>
</td>
</tr>
