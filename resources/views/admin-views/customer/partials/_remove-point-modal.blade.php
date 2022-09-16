@php
  $canAddPoint = $customer->point === 0 ? false : true;
@endphp
<div class="modal-header">
  <p class="mb-0">{{ translate('remove wallet points') }}</p>
</div>
<div class="modal-body">
  <div class="d-flex justify-content-between">
    <p class="mb-0 badge badge-soft-info">{{ $customer->name }}</p>
    <p class="mb-0 font-weight-bold"> ({{ translate('Available Point : ') }} <span id="current_points">{{ $customer->point }}</span>)</p>
  </div>

  <form action="{{ route('admin.customer.remove-points', $customer->id) }}" method="POST" class="my-3">
    @csrf
    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
    <div class="form-group">
      <label for="points">{{ translate('Remove point') }}: </label>
      <input type="number" name="points" id="points" class="form-control" min="0" value="0" step="1" {{ !$canAddPoint ? 'disabled': '' }}>
      @if (!$canAddPoint)
      <span class="form-text small text-danger">{{ translate("you can't remove points when credit is zero") }}</span>
      @endif
    </div>
    <div class="form-group">
      <button class="btn btn-primary" type="submit" {{ !$canAddPoint ? 'disabled': '' }}>
        <i class="tio-remove"></i> {{ translate('remove') }}
      </button>
      <button class="btn btn-dark" type="button" data-dismiss="modal">
        <i class=" tio-clear"></i> {{ translate('cancel') }}
      </button>
    </div>
  </form>

</div>

<script>
  var currentCustomerPoints = parseInt("{{ $customer['point'] }}")
  document.getElementById('points').addEventListener('change', function (event) {
    var value = parseInt(event.target.value);
    console.log(value);
    document.getElementById('current_points').innerText = currentCustomerPoints - value;
  });
</script>
