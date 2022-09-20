<div class="list-group list-group-flush">
  @foreach ($users as $user)
    <div class="list-group-item list-group-item-action" style="cursor: pointer;">
      <p  class="nav-link" onclick="viewConvs('{{route('admin.message.view',[$user->id])}}','customer-{{$user->id}}')">
        <span>{{ $user->name }}</span>
        <span class="badge badge-info">{{ $user->conversations_count }}</span>
      </p>
    </div>
  @endforeach
</div>

