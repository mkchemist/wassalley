<div class="col-12">
  <div>
    {{ translate('total') }} <span class="badge badge-soft-primary">{{ $products->total() }}</span>
  </div>
  <form action="{{ route('admin.orders.edit', $order->id) }}" method="GET" id="products_search_form">
    <div class="d-flex justify-content-between">
      <div class="my-2 form-inline">
        <label for="category" class="">{{ translate('category') }}</label>
        <select name="category" id="category" class="form-control form-control-sm col mx-2">
          <option value="">{{ translate('all') }}</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id }}" {{ (int) request('category') === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="text-right form-inline">
        <input type="search" name="search" id="search" class="form-control form-control-sm col-auto"
          placeholder="{{ translate('search') }}" value="{{ request('search') }}">
        <button class="btn btn-info btn-sm mx-2">{{ translate('search') }}</button>
      </div>

    </div>
  </form>
  @if (count($products))
  <div class="row mx-auto">
    @foreach ($products as $product)
    <div class="col-lg-3 p-2">
      <div class="card">
        <a href="#" onclick="showProductCartModal({{ $product->id }})">
          <img src="{{ asset('storage/app/public/product/'.$product->image) }}" alt="" class="card-img-top"
            height="100">
        </a>
        <div class="card-body">
          <p class="mb-0 text-primary">{{ $product->name }}</p>
          <p class="mb-0">{{ \App\CentralLogics\Helpers::set_symbol($product->price) }}</p>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @else
    <div class="p-5 text-center text-info">
      <span class="tio-checkmark-circle tio-xl"></span>
      <p>{{ translate('No data to show') }}</p>

    </div>
  @endif

  <div>
    {{ $products->withQueryString()->links() }}
  </div>
</div>



@push('script')
<script>


    document.getElementById('category').addEventListener('change', function () {
      var form = document.getElementById('products_search_form');
      form.submit();
    });

    document.getElementById('search').addEventListener('change', function (event) {
      console.log('changed');
      var value = event.target.value;
      var form = document.getElementById('products_search_form');
      if (value === "") {
        form.submit();
      }
    })

</script>
@endpush
