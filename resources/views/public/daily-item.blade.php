<article class="public-dish">
@if(!empty($item['photo']['web_url']))<img class="daily-photo" src="{{ $item['photo']['web_url'] }}" alt="{{ $item['name'] }}" loading="lazy" style="object-position:{{ ($item['photo']['focal_x']??.5)*100 }}% {{ ($item['photo']['focal_y']??.5)*100 }}%">@endif
<strong>{{ $item['name'] }}</strong>
@if(!empty($item['description']))<p style="font-size:16px">{{ $item['description'] }}</p>@endif
@if(!empty($item['allergens']))<p class="muted" style="font-size:14px">Alergénios: {{ implode(', ',\App\Domain\Restaurant\Allergens::labels($item['allergens'])) }}.</p>@endif
</article>
