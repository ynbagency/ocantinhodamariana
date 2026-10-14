<div class="daily-slot">
<label>{{ $label }} da carta<select class="daily-picker" name="{{ $key }}_source_id" data-catalogue-slot="{{ $key }}"><option value="">Escrever um prato ocasional</option>
@foreach($catalogue->filter(fn($item)=>$item->category->kind===($key==='soup'?'soup':'dish')) as $option)
<option value="{{ $option->id }}" data-name="{{ $option->name }}" data-photo="{{ $option->media_asset_id }}" @selected(old($key.'_source_id',$current?->menu_item_id)==$option->id)>{{ $option->name }}</option>
@endforeach
</select></label>
<label>{{ $label }}<input name="{{ $key }}" value="{{ old($key,$current?->name) }}" autocomplete="off" maxlength="160" placeholder="Nome do prato" data-slot-name="{{ $key }}"></label>
<input type="hidden" name="{{ $key }}_available" value="0"><label class="availability-check"><input type="checkbox" name="{{ $key }}_available" value="1" @checked(old($key.'_available',$current?->available??true))> {{ $label }} disponível</label>
<label>Fotografia · {{ $label }}<select class="daily-picker" name="{{ $key }}_photo_id" data-slot-photo="{{ $key }}"><option value="">Sem fotografia</option>@foreach($media as $asset)<option value="{{ $asset->id }}" @selected(old($key.'_photo_id',$current?->media_asset_id)==$asset->id)>{{ $asset->title?:'Fotografia '.$asset->id }}</option>@endforeach</select></label>
@if($current?->media)<img class="catalogue-photo" src="{{ $current->media->publicUrl('thumb') }}" alt="{{ $current->name }}">@endif
</div>
