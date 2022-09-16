@extends('layouts.admin.app')

@section('title', translate('Customer List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center mb-3">
                <div class="col-sm">
                    <h1 class="page-header-title">{{translate('customers')}}
                        <span class="badge badge-soft-dark ml-2">({{ $customers->total() }})</span>
                    </h1>
                </div>
            </div>
            <!-- End Row -->

            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
              <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                <i class="tio-chevron-left"></i>
              </a>
            </span>

                <span class="hs-nav-scroller-arrow-next" style="display: none;">
              <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                <i class="tio-chevron-right"></i>
              </a>
            </span>

                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active"
                           href="#">{{translate('customer List')}}</a>
                    </li>
                    {{--<li class="nav-item">
                        <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Open</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Unfulfilled</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" tabindex="-1" aria-disabled="true">Unpaid</a>
                    </li>--}}
                </ul>
                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header flex-end">
              <form action="{{ url()->current() }}" method="GET" id="customerStateForm">
                <div class="d-flex">
                  <div class="form-check-inline mx-2">
                    <input type="radio" name="customer_state"  class='form-check-input' {{ request('customer_state') && request('customer_state') === 'all' ? 'checked': '' }} value="all">
                    <label for="" class="form-check-label">{{ translate('all') }}</label>
                  </div>
                  <div class="form-check-inline mx-2">
                    <input type="radio" name="customer_state"  class='form-check-input' {{ !request('customer_state') || request('customer_state') === 'active' ? 'checked': '' }} value="active">
                    <label for="" class="form-check-label">{{ translate('current') }}</label>
                  </div>
                  <div class="form-check-inline mx-2">
                    <input type="radio" name="customer_state"  class='form-check-input' {{ request('customer_state') && request('customer_state') === 'inactive' ? 'checked': '' }} value="inactive">
                    <label for="" class="form-check-label">{{ translate('deleted') }}</label>
                  </div>

                </div>
              </form>
                <div class="">
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                   class="form-control"
                                   placeholder="{{translate('Search')}}" aria-label="Search"
                                   value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="input-group-text"><i class="tio-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="">
                <table class="table table-hover table-borderless table-thead-bordered  table-align-middle card-table pb-5 table-sm"
                       style="width: 100%;">
                    <thead class="thead-light">
                    <tr>
                        <th class="">
                            {{translate('#')}}
                        </th>
                        <th class="table-column-pl-0">{{translate('name')}}</th>
                        <th>{{translate('email')}}</th>
                        <th>{{translate('phone')}}</th>
                        <th>{{translate('total')}} {{translate('order')}}</th>
                        <th>Payment</th>
                        <th>{{translate('available')}} {{translate('points')}}</th>
                        <th>{{ translate("Status") }}</th>
                        <th>{{translate('actions')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @include('admin-views.customer.partials._table',['customers'=>$customers])
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <div class="row">
                    <div class="col-12" style="overflow-x: scroll;">
                        {!! $customers->links() !!}
                    </div>
                </div>
            </div>
            <!-- End Footer -->
        </div>
        <!-- End Card -->

        <div class="modal fade" id="add-point-modal" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="modal-content"></div>
            </div>
        </div>


        <div class="modal fade" id="remove_point_modal">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

            </div>
          </div>
        </div>

    </div>
@endsection

@push('script_2')
    <script src="{{ asset("assets/admin/js/sweet_alert.js") }}"></script>
    <script>

        @if (session('success'))

        Swal.fire({
            title: "{{ session('success') }}",
            type: "success",

        })
        @endif
        @if (session('error'))

        Swal.fire({
            title: "{{ session('error') }}",
            type: "error",

        })
        @endif

        $('#search-form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.customer.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('.card-footer').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        function add_point(form_id, route, customer_id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: route,
                data: $('#' + form_id).serialize(),
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('.show-point-' + customer_id).text('( {{translate('Available Point : ')}} ' + data.updated_point + ' )');
                    $('.show-point-' + customer_id + '-table').text(data.updated_point);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }

        function set_point_modal_data(route) {
            $.get({
                url: route,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#add-point-modal').modal('show');
                    $('#modal-content').html(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }


      [].slice.call(document.querySelectorAll('[name="customer_state"]')).forEach((radio) => {
        radio.addEventListener('click', function () {
          document.getElementById('customerStateForm').submit();
        })
      });

      /**
       * Delete selected row
       * @param {HTMLElement} current button
       * @param {Number} id [customer id]
       */
      function deleteCustomer(element, id) {
        window.event.preventDefault();
        Swal.fire({
          title: "{{ translate('Are you sure to delete this') }}",
          showCancelButton: true,
          cancelButtonText: "{{ translate('no') }}",
          confirmButtonText: "{{ translate('yes') }}",
          type: 'warning',
          cancelButtonColor: '#282830',
          confirmButtonColor: '#f10500'
        }).then((response) => {
          if (response.value) {
            var url = `{{ url('admin/customer/delete') }}/${id}`;
            element.parentElement.action = url;
            element.parentElement.submit();
          }
        })
      }
      /**
       * Restore selected row
       * @param {HTMLElement} current button
       * @param {Number} id [customer id]
       */
      function restoreCustomer(element, id) {
        window.event.preventDefault();
        Swal.fire({
          title: "Are you sure to restore this",
          showCancelButton: true,
          cancelButtonText: "{{ translate('no') }}",
          confirmButtonText: "{{ translate('yes') }}",
          type: 'warning',
          cancelButtonColor: '#282830',
          confirmButtonColor: '#180'
        }).then((response) => {
          if (response.value) {
            var url = `{{ url('admin/customer/restore') }}/${id}`;
            element.parentElement.action = url;
            element.parentElement.submit();
          }
        })
      }

      function removeCustomerPoints(url) {
        fetch(url)
        .then((res) => res.json())
        .then((data) => {
          $('#remove_point_modal .modal-content').html(data.view);
          $('#remove_point_modal').modal('show');
        }).catch((err) => {
          console.log(err);
        })
      }

    </script>
@endpush
