<ul class="nav flex-column">
  @foreach ($categories as $item)
  <li class="nav-item my-2">
    <div class="form-check-inline">
      <input type="checkbox" name="category[]" id="tree_input{{ $item['id'] }}" value="{{ $item['id'] }}" class="form-check-input tree-check-input" data-toggle="collapse" data-target="#parent{{ $item['id'] }}_collapse" {{ $item['parent_id'] ? 'data-parent-id=tree_input'.$item['parent_id'] : null }} {{ in_array($item['id'], $branchCategories) ? 'checked': null }}>
      <label class="form-check-label lead">{{ $item['name'] }}</label>
    </div>
    @if (count($item['childes']))
    <div class="collapse p-3 border rounded border-info" id="parent{{ $item['id'] }}_collapse">
      <x-category-tree-component :categories="$item['childes']" :branch-categories="$branchCategories" />
    </div>
    @endif
  </li>

  @endforeach
</ul>
