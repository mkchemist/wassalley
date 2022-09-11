@php
$currentVariant = collect($variations)->filter(function ($item) use($product) {
  //dd($item->price, $product->price, (float) $item->price, (float) $product->price );
  return (float)$item->price === (float)$product->price;
})->first();

if ($currentVariant) {
  $currentVariant = explode('-', $currentVariant->type);
}


@endphp


<style>
  .checked-btn-check {
    background-color: orangered;
    color: white;
  }

  .qty-form {
    margin:5px 0;
    position:relative;
    width:100%;
    display:inline-flex;
  }
  .qty-form button {
    width:25%;
    border:1px solid #1113;
    background-color: #282830;
    color:white;
    border-radius: 5px;
  }

  .qty-form input {
    width:50%;
    border:none;
    text-align: center;
  }
</style>


<div class="modal-body">
  {{-- product info --}}
  <div class="row mx-auto">
    <div class="col-md-5">
      <img src="{{ asset('storage/app/public/product/'.$product->image) }}" alt="{{ $product->name }} image"
        class="img-fluid rounded">
    </div>
    <div class="col-md-7">
      <h4 class="text-primary">{{ $product->name }}</h4>
      <h5>{{ \App\CentralLogics\Helpers::set_symbol($product->price) }}</h5>
    </div>
  </div>
  {{-- end product info. --}}
  <form action="{{ route('admin.orders.product.add') }}" method="POST">
    @csrf
    <input type="hidden" name="order_id" value="{{ $order_id }}">
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    {{-- product attribute --}}
    <div class="my-2">
      <p class="lead">{{ translate('attribute') }}</p>
      @foreach ($attributes as $choice)
      <div class="row mx-auto my-2">
        <div class="col-md-2">{{ $choice->title }}</div>
        <div class="col-md-10 row mx-auto">
          @foreach ($choice->options as $key => $option)
          <div class="col-md-auto border rounded  mx-2 btn-check {{ in_array($option, $currentVariant) ? 'checked-btn-check': '' }}" data-selector="{{ $choice->name }}">
            <input type="radio" name="{{ $choice->name }}" id="{{ $choice->name }}_{{ $key }}" class='d-none'
              value="{{ $option }}" {{ in_array($option, $currentVariant) ? 'checked': '' }}>
            <label for="{{ $choice->name }}_{{ $key }}">{{ $option }}</label>
          </div>
          @endforeach
        </div>
      </div>
      @endforeach
    </div>
    {{-- end product attribute --}}
    {{-- product quantity --}}
    <div class="my-2">
      <div class="row mx-auto justify-content-between align-items-center">
        <div class="col-md px-0">
          <p class="mb-0 lead">{{ translate('Quantity') }}</p>
        </div>
        <div class="col-md-auto form-inline">
          <span class="">{{ $product->unit->name }}</span>
          <button class="btn btn-sm btn-light border mx-1" type="button" onclick="updateProductQuantity('quantity', 'dec')">-</button>
          <input type="number" min="{{ $product->unit->quantity }}" name="quantity" id="quantity" value="{{ $product->unit->quantity }}" class="form-control form-control-sm col-6 mx-1" step="{{ $product->unit->quantity }}" readonly>
          <button class="btn btn-sm btn-light border" type="button" onclick="updateProductQuantity('quantity', 'inc')">+</button>
        </div>
      </div>
    </div>
    {{-- end product quantity --}}
    {{-- products addon --}}
    <div class="my-2">
      <p class="mb-0 lead">{{ translate('addon') }}</p>
      @if (count($addOns) > 0)
      <div class="row mx-auto">
        @foreach ($addOns as $key => $addOn)
          <div class="col-md-3 mx-1 px-0">
            <div class="d-flex align-items-center justify-content-center border rounded" data-indicator="addon_{{ $key }}" style="height:100px;cursor: pointer" onclick="updateAddOnQuantity('addon_{{ $key }}', 'inc')" >
              <div class="font-weight-bold">
                <p class="mb-0">{{ $addOn->name }}</p>
                <p class="mb-0">{{ \App\CentralLogics\Helpers::set_symbol($addOn->price) }}</p>
              </div>
            </div>
            <div class="qty-form d-none" id="qty_form_addon_{{ $key }}">
              <button type="button" onclick="updateAddOnQuantity('addon_{{ $key }}', 'dec')">-</button>
              <input type="hidden" name="add_on_id[]" value="{{ $addOn->id }}">
              <input type="number" min="0" name="add_on_qtys[]" id="addon_{{ $key }}" value="0" data-item="{{ $addOn->name }}" data-price="{{ $addOn->price }}" step="1" >
              <button type="button" onclick="updateAddOnQuantity('addon_{{ $key }}', 'inc')">+</button>
            </div>
          </div>
        @endforeach
      </div>
      @endif
    </div>
    {{-- end products addon --}}
    {{-- Order Total Price --}}
    <div class="my-3 h3">
      {{ translate('total') }} {{ translate('price') }}:
      <span id="order_total_price">{{ $product->price * $product->unit->quantity }}</span>
    </div>
    {{-- End Order Total Price --}}
    <div class="text-center">
      <button class="btn btn-primary">
        <span class=" tio-shopping-basket"></span>
        {{ translate('add') }}
      </button>
    </div>
  </form>

</div>



<script>
  var btnChecks = Array.prototype.slice.call(document.querySelectorAll('.btn-check'));
  var productPrice = parseFloat("{{ $product->price }}");
  var productQuantity = parseFloat("{{ $product->unit->quantity }}");
  var addOns = {};
  var variations = @json($variations);
  var currentProductVariation = getCurrentVariant();
  function getCurrentVariant() {
    var variants = [];
    btnChecks.forEach((item) => {
      let radio = item.querySelector('input[type="radio"]');
      if (radio.checked === true) {
        variants.push(radio.value);
      }
    });

    let selectedVariant = variations.filter((item) => {
      return item.type === variants.map(item => item.trim()).join('-');
    })[0];
    if (selectedVariant) {
      return selectedVariant
    }
    return {
      type: '',
      price: productPrice
    };
  }

  function updateOrderPrice() {
    var total = (parseFloat(productQuantity) * parseFloat(currentProductVariation.price));
    var addOnPrice = 0;
    Object.keys(addOns).forEach((item) => {
      let row = addOns[item];
      total += parseFloat(row.price * row.quantity);
    });


    $('#order_total_price').html(total);
  }

  btnChecks.forEach((el) => {
    var elRadio = el.querySelector('input[type="radio"]');
    el.addEventListener('click', function() {
      elRadio.click();
      [].slice.call(document.querySelectorAll(`[data-selector="${el.dataset.selector}"]`)).forEach((item) => item.classList.remove('checked-btn-check'));
      el.classList.add('checked-btn-check');
      currentProductVariation = getCurrentVariant();
      updateOrderPrice();
    })
  });


  function updateProductQuantity(target, dir) {
    var el = document.getElementById(target);
    var step = parseFloat(el.step), min= parseFloat(el.min), val = parseFloat(el.value);
    if (val === min && dir === 'dec') {
      return;
    }
    if (dir === 'inc') {
      val += step;
    } else {
      val -= step;
    }
    el.value = val;
    productQuantity = val;
    updateOrderPrice();
  }

  function updateAddOnQuantity(target, dir) {
    var el = document.getElementById(target);
    var name = el.dataset.item,
        price = parseFloat(el.dataset.price),
        val = parseFloat(el.value),
        min = parseFloat(el.min),
        step = parseFloat(el.step);
    if (val === min && dir === "dec") {
      return;
    }
    if (dir === 'inc') {
      val += step;
    } else {
      val -= step;
    }
    el.value = val;
    console.log(el)
    if (!addOns[name]) {
      addOns[name] = {
        price,
        quantity: 0
      }
    }
    var indicatorElement = document.querySelector(`[data-indicator="${target}"]`);
    if (val > min) {
      indicatorElement.classList.add('checked-btn-check');
      $(`#qty_form_${target}`).removeClass('d-none');

    } else {
      indicatorElement.classList.remove('checked-btn-check');
      $(`#qty_form_${target}`).addClass('d-none');
    }

    addOns[name].quantity = val;

    updateOrderPrice();
  }

</script>
