@extends('layouts.admin.app')

@push('css_or_js')
<style>
  #details-table td {
    padding: 2px;
  }
</style>
@endpush

@section('content')
@php
$order = session('order_carts');
$totalOrderItemsPrice = 0;
$totalOrderAddOnsPrice = 0;
$totalOrderTax = 0;
@endphp
<div class="content container-fluid">
  <div class="page-header d-print-none">
    <div class="">
      <h2><span class="tio-edit"></span> Edit Order #{{ $order->id }}</h2>
      <a href="{{ url()->previous() }}" class="btn btn-dark">Back</a>
    </div>
  </div>
  <main class="mt-5">
    <div class="row mx-auto">
      {{-- Order Details section --}}
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <div class="card-header-title h4">
              Order Details <span class="badge badge-danger">{{ count($order->details) }}</span>
            </div>
          </div>
          {{-- Additional Products --}}
          <div class="card-header">
            @include('admin-views.order.partials.edit-order-products-list')
          </div>
          {{-- End Additional Products --}}
          <div class="card-body">
            <table class="table table-borderless" id="details-table">
              @foreach($order->details as $detail)
              @php
              $product = json_decode($detail->product_details);
              $variations = $detail->variation ? json_decode($detail->variation) : [];
              $actualProductPrice = (count($variations) ? $variations[0]->price : $product->price) - $detail->discount_on_product;
              $totalOrderProductPrice = ($actualProductPrice) * $detail->quantity;
              $totalOrderItemsPrice += $totalOrderProductPrice;
              $addOnIds = json_decode($detail->add_on_ids);
              $addOns = count($addOnIds) ? \App\Model\AddOn::whereIn('id', $addOnIds)->get() : [];
              $addOnQuantities = json_decode($detail->add_on_qtys);
              $totalOrderTax += $detail->tax_amount * $detail->quantity;

              @endphp
              <tbody class="border-bottom">
                <tr>
                  <td>
                    <div class="my-2">
                      <a href="#" onclick="showProductCartModal({{ $detail->product_id }})">

                        <img src="{{ asset('storage/app/public/product/'.$product->image) }}" alt=""
                          class="img-fluid rounded" width="75">
                      </a>
                    </div>
                    <form action="{{ route('admin.orders.product.delete', $detail->id) }}" method="POST">
                      @csrf
                      @method("DELETE")
                      <button class="btn btn-danger btn-sm delete-btn" type="button">
                        <span class="tio-delete"></span>
                      </button>
                    </form>
                  </td>
                  <td colspan="2" class="font-weight-bold">
                    <p class="mb-0 text-primary">{{ $product->name }}</p>
                    <p class="mb-0 small">{{ $product->description }}</p>
                  </td>
                  <td class="font-weight-bold text-dark">{{ \App\CentralLogics\Helpers::set_symbol($actualProductPrice)
                    }}</td>
                  <td class="font-weight-bold text-dark">{{ $detail->quantity }}</td>
                  <td class="font-weight-bold text-dark">{{
                    \App\CentralLogics\Helpers::set_symbol($totalOrderProductPrice) }}</td>
                </tr>
                <tr>
                  <td></td>
                  <td colspan="2" class="text-info">Tax</td>
                  <td>{{ $detail->tax_amount }}</td>
                  <td></td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($detail->tax_amount * $detail->quantity) }}</td>
                </tr>
                @if (count($variations))
                @foreach ($variations as $variant)
                <tr>
                  <td></td>
                  <td colspan="4" class="font-weight-bold text-info">variations</td>
                </tr>
                <tr class="small">
                  <td></td>
                  <td>
                    @if ($variant->type)
                    {{ translate('type') }}: {{ $variant->type }}
                    @endif
                  </td>
                  <td></td>
                  <td>{{ $variant->price }}</td>
                  <td></td>
                  <td></td>
                </tr>
                @endforeach
                @endif
                <tr>
                  <td></td>
                  <td colspan="4" class="font-weight-bold text-info">Add on</td>
                </tr>
                @foreach ($addOns as $key => $addOn)
                @php
                $totalOrderAddOnsPrice += $addOn->price * $addOnQuantities[$key];
                @endphp
                <tr class="small">
                  <td></td>
                  <td> {{ $addOn->name }}</td>
                  <td></td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($addOn->price) }}</td>
                  <td>{{ $addOnQuantities[$key] }}</td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($addOn->price * $addOnQuantities[$key]) }}</td>
                </tr>
                @endforeach
              </tbody>
              @endforeach
              <tbody class="my-5 text-dark font-weight-bold">
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Items Price:</td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($totalOrderItemsPrice) }}</td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Tax / Vat:</td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($totalOrderTax) }}</td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Addon Cost:</td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($totalOrderAddOnsPrice) }}</td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="3" class="border-bottom"></td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Subtotal:</td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($totalOrderAddOnsPrice+$totalOrderTax +
                    $totalOrderItemsPrice) }}</td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Coupon Discount:</td>
                  <td>-{{ \App\CentralLogics\Helpers::set_symbol($order->coupon_discount_amount) }}</td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Extra Discount:</td>
                  <td>-{{ \App\CentralLogics\Helpers::set_symbol($order->extra_discount) }}</td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2">Delivery Fee:</td>
                  <td>{{ \App\CentralLogics\Helpers::set_symbol($order->delivery_charge) }}</td>
                </tr>

                <tr>
                  <td colspan="3"></td>
                  <td colspan="3" class="border-bottom"></td>
                </tr>
                <tr>
                  <td colspan="3"></td>
                  <td colspan="2" class="text-primary font-weight-bolder">Total:</td>
                  <td class="text-dark font-weight-bolder">{{
                    \App\CentralLogics\Helpers::set_symbol(
                    $totalOrderAddOnsPrice+
                    $totalOrderTax +
                    $totalOrderItemsPrice +
                    $order->delivery_charge+
                    $order->coupon_discount_amount-
                    $order->extra_discount
                    ) }}</td>
                </tr>
              </tbody>
            </table>
            <hr>
            <div class="text-right">
              <button class="btn btn-primary">{{ translate('save') }}</button>
              <button class="btn btn-dark">{{ translate('cancel') }}</button>
            </div>
          </div>
        </div>
      </div>
      {{-- End order details section --}}
      {{-- Customer details --}}
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">
            <div class="card-header-title h4">
              Customer Details
            </div>
          </div>
          <!-- Body -->
          <div class="card-body">
            <div class="media align-items-center" href="javascript:">
              <div class="avatar avatar-circle mr-3">
                <a href="{{route('admin.customer.view',[$order->customer['id']])}}">
                  <img class="avatar-img" style="width: 75px"
                    onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                    src="{{asset('storage/app/public/profile/'.$order->customer->image)}}" alt="Image Description">
                </a>
              </div>
              <div class="media-body">
                <span class="text-body text-hover-primary">
                  <a href="{{route('admin.customer.view',[$order->customer['id']])}}">
                    {{$order->customer['f_name']." ".$order->customer['l_name']}}
                  </a>
                </span>
              </div>
              <div class="media-body text-right">
                {{--<i class="tio-chevron-right text-body"></i>--}}
              </div>
            </div>

            <hr>

            <div class="media align-items-center" href="javascript:">
              <div class="icon icon-soft-info icon-circle mr-3">
                <i class="tio-shopping-basket-outlined"></i>
              </div>
              <div class="media-body">
                <span
                  class="text-body text-hover-primary">{{\App\Model\Order::where('user_id',$order['user_id'])->count()}}
                  {{translate('orders')}}</span>
              </div>
              <div class="media-body text-right">
                {{--<i class="tio-chevron-right text-body"></i>--}}
              </div>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center">
              <h5>{{translate('contact')}} {{translate('info')}}</h5>
            </div>

            <ul class="list-unstyled list-unstyled-py-2">
              <li>
                <i class="tio-online mr-2"></i>
                {{$order->customer['email']}}
              </li>
              <li>
                <i class="tio-android-phone-vs mr-2"></i>
                {{$order->customer['phone']}}
              </li>
            </ul>

            @if($order['order_type']!='take_away')
            <hr>
            @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
            <div class="d-flex justify-content-between align-items-center">
              <h5>{{translate('delivery')}} {{translate('address')}}</h5>
              @if(isset($address))
              <a class="link" data-toggle="modal" data-target="#shipping-address-modal"
                href="javascript:">{{translate('edit')}}</a>
              @endif
            </div>
            @if(isset($address))
            <span class="d-block">
              {{$address['contact_person_name']}}<br>
              {{$address['contact_person_number']}}<br>
              {{$address['address_type']}}<br>
              <a target="_blank"
                href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}">
                <i class="tio-map"></i> {{$address['address']}}<br>
              </a>
            </span>
            @endif
            @endif
          </div>
          <!-- End Body -->
        </div>
      </div>
      {{-- end custome details --}}
    </div>
  </main>
</div>

<!-- Modal -->
<div id="shipping-address-modal" class="modal fade" tabindex="-1" role="dialog"
  aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <!-- Header -->
      <div class="modal-top-cover bg-dark text-center">
        <figure class="position-absolute right-0 bottom-0 left-0" style="margin-bottom: -1px;">
          <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 1920 100.1">
            <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z" />
          </svg>
        </figure>

        <div class="modal-close">
          <button type="button" class="btn btn-icon btn-sm btn-ghost-light" data-dismiss="modal" aria-label="Close">
            <svg width="16" height="16" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
              <path fill="currentColor"
                d="M11.5,9.5l5-5c0.2-0.2,0.2-0.6-0.1-0.9l-1-1c-0.3-0.3-0.7-0.3-0.9-0.1l-5,5l-5-5C4.3,2.3,3.9,2.4,3.6,2.6l-1,1 C2.4,3.9,2.3,4.3,2.5,4.5l5,5l-5,5c-0.2,0.2-0.2,0.6,0.1,0.9l1,1c0.3,0.3,0.7,0.3,0.9,0.1l5-5l5,5c0.2,0.2,0.6,0.2,0.9-0.1l1-1 c0.3-0.3,0.3-0.7,0.1-0.9L11.5,9.5z" />
            </svg>
          </button>
        </div>
      </div>
      <!-- End Header -->

      <div class="modal-top-cover-icon">
        <span class="icon icon-lg icon-light icon-circle icon-centered shadow-soft">
          <i class="tio-location-search"></i>
        </span>
      </div>

      @php($address=\App\Model\CustomerAddress::find($order['delivery_address_id']))
      @if(isset($address))
      <form action="{{route('admin.orders.update-shipping',[$order['delivery_address_id']])}}" method="post">
        @csrf
        <div class="modal-body">
          <div class="row mb-3">
            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
              {{translate('type')}}
            </label>
            <div class="col-md-10 js-form-message">
              <input type="text" class="form-control" name="address_type" value="{{$address['address_type']}}" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
              {{translate('contact')}}
            </label>
            <div class="col-md-10 js-form-message">
              <input type="text" class="form-control" name="contact_person_number"
                value="{{$address['contact_person_number']}}" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
              {{translate('name')}}
            </label>
            <div class="col-md-10 js-form-message">
              <input type="text" class="form-control" name="contact_person_name"
                value="{{$address['contact_person_name']}}" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
              {{translate('address')}}
            </label>
            <div class="col-md-10 js-form-message">
              <input type="text" class="form-control" name="address" value="{{$address['address']}}" required>
            </div>
          </div>
          <div class="row mb-3">
            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
              {{translate('latitude')}}
            </label>
            <div class="col-md-4 js-form-message">
              <input type="text" class="form-control" name="latitude" value="{{$address['latitude']}}" required>
            </div>
            <label for="requiredLabel" class="col-md-2 col-form-label input-label text-md-right">
              {{translate('longitude')}}
            </label>
            <div class="col-md-4 js-form-message">
              <input type="text" class="form-control" name="longitude" value="{{$address['longitude']}}" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-white" data-dismiss="modal">{{translate('close')}}</button>
          <button type="submit" class="btn btn-primary">{{translate('save')}} {{translate('changes')}}</button>
        </div>
      </form>
      @endif
    </div>
  </div>
</div>
<!-- End Modal -->

{{-- Modal --}}
<div class="modal fade" id="view_product_details">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    </div>
  </div>
</div>
{{-- end Modal --}}
@endsection


@push('script')
<script>
  [].slice.call(document.querySelectorAll('.delete-btn')).forEach((btn) => {
      btn.addEventListener('click', function (event) {
        Swal.fire({
          title: 'Warning',
          icon: 'question',
          text: 'Are you sure to delete this?',
          showCancelButton: true,
          confirmButtonColor: '#f71e1e',
          cancelButtonColor: '#2196f3'
        }).then((response) => {
          if(response.value) {
            btn.parentElement.submit();
          }
        });
      })
    });


    function showProductCartModal(id) {
      window.event.preventDefault();
      fetch(`{{ url('admin/orders') }}/product/show/${id}?order_id={{ $order->id }}`)
      .then((res) => {
        return res.text();
      }).then((res) => {
        $('#view_product_details .modal-content').html(res);
        $('#view_product_details').modal('show');
      })

    }
</script>

@endpush
