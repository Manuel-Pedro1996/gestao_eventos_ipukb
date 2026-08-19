@props(['url'])
<tr>
<td class="header" style="text-align: center; padding: 25px 0;">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
    @if (file_exists(public_path('img/ti.png')))
        <img src="{{ config('app.url') }}/img/ti.png" class="logo" alt="{{ config('app.name') }}" style="max-height: 50px; width: auto; display: block; margin: 0 auto;">
    @else
        <span style="font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.5px;">
            {{ config('app.name') }}
        </span>
    @endif
</a>
</td>
</tr>