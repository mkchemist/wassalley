@foreach($customers as $key=>$customer)
    <tr class="">
        <td class="">
            {{$customers->firstitem()+$key}}
        </td>
        <td class="table-column-pl-0">
            <a href="{{route('admin.customer.view',[$customer['id']])}}">
                {{$customer['f_name']." ".$customer['l_name']}}
            </a>
        </td>
        <td>
            {{$customer['email']}}
        </td>
        <td>
            {{$customer['phone']}}
        </td>
        <td>
            <label class="badge badge-soft-info">
                {{$customer->orders->count()}}
            </label>

        <td><span class="badge badge-danger">{{ \App\CentralLogics\Helpers::set_symbol($customer->totalPayment ) }}</span></td>
        <td class="show-point-{{$customer['id']}}-table">
            {{$customer['point']}}
        </td>
        <td>
            <span class="badge badge-info">
                {{ $customer['is_active'] ? translate("active") : translate("inactive") }}
            </span>

        </td>
        </td>
        <td>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="tio-settings"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item"
                       href="{{route('admin.customer.view',[$customer['id']])}}">
                        <i class="tio-visible"></i> {{translate('view')}}
                    </a>
                    <a href="{{ route('admin.customer.edit', $customer['id']) }}" class="dropdown-item">
                        <i class="tio-edit"></i> {{ __('messages.edit') }}
                    </a>
                    <a class="dropdown-item" href="javascript:" onclick="set_point_modal_data('{{route('admin.customer.set-point-modal-data',[$customer['id']])}}')">
                        <i class="tio-coin"></i> {{translate('Add Point')}}
                    </a>
                    <a href="" class="dropdown-item toggleStatusBtn" data-id="{{ $customer['id'] }}">
                        <i class="tio-user-switch"></i>
                        <span>{{ translate('status') }}</span>
                    </a>

                    {{--<a class="dropdown-item" target="" href="">
                        <i class="tio-download"></i> Suspend
                    </a>--}}
                </div>
            </div>
        </td>
    </tr>
<!--    <div class="modal fade" id="exampleModal-{{$customer['id']}}" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Internal Point</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="javascript:" method="POST" id="point-form-{{$customer['id']}}">
                    @csrf
                    <div class="modal-body">
                        <h5>
                            <label class="badge badge-soft-info">
                                {{$customer['f_name']}} {{$customer['l_name']}}
                            </label>
                            <label class="show-point-{{$customer['id']}}">
                                ( Available Point : {{$customer['point']}} )
                            </label>
                        </h5>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Add Point :</label>
                            <input type="number" min="1" value="1" max="1000000"
                                   class="form-control"
                                   name="point">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                        <button type="button"
                                onclick="add_point('point-form-{{$customer['id']}}','{{route('admin.customer.add-point',[$customer['id']])}}','{{$customer['id']}}')"
                                class="btn btn-primary">Add
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>-->

    <form action="{{ url('admin/customer/update-state') }}/@id" id="statusForm" method="POST">
        @csrf
    </form>
@endforeach


@push('script_2')
    <script>

        $(document).ready(function () {

            $('.toggleStatusBtn').each(function (index, btn) {
                /** Change selected customer status */
                $(btn).click(function (event) {
                    event.preventDefault();
                    var statusForm = $('#statusForm');
                    statusForm.attr('action', statusForm.attr('action').replace('@id', $(this).data('id')))
                    .submit();
                })

            });

        });

    </script>
@endpush
