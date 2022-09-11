@extends('layouts.admin.app')

@section('title', 'Admin | Units')

@section('content')

<div>
    <div class="container">

        {{-- Units Form --}}
        <form action="{{ route('admin.units.store') }}" method="POST" class="p-lg-3 p-2 border rounded mt-3">
            @csrf
            <h4>{{ __("messages.add new unit") }}</h4>
            <div class="row mx-auto form-group">
                <div class="col-lg-6">
                    <label for="name">{{ __('messages.name') }}</label>
                    <input type="text" name="name" id="name" class="form-control form-control-sm @error('name') border border-danger @enderror" placeholder="Unit name" value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger small">{{ implode(', ', $errors->get('name')) }}</span>
                    @enderror
                </div>
                <div class="col-lg-6">
                    <label for="name">Minimum quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control form-control-sm @error('quantity') border border-danger @enderror" value="{{ old('quantity') ?? 0.1 }}" min="0.1" step="0.005">
                    @error('quantity')
                    <span class="text-danger small">{{ implode(', ', $errors->get('quantity')) }}</span>
                @enderror
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary">{{ __("messages.Submit") }}</button>
            </div>
        </form>

        {{-- Units Table --}}
        <div class="p-lg-3 p-2 mt-3">
            <div class="mb-3">
                <form action="{{ route('admin.units.index') }}">
                    <div class="form-inline">
                        <input type="search" name="search" id="search" class="form-control" placeholder="search..." value="{{ request('search') }}">
                        <button class="btn btn-info mx-2">search</button>
                    </div>
                </form>
            </div>
            <table class="table table-striped table-borderless table-thead-bordered card-table">
                <thead>
                    <tr>
                        <th>Unit name</th>
                        <th>Unit Minimum qunatity</th>
                        <th>NUmber of products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                    <tr>

                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->quantity }}</td>
                        <td>{{ $unit->products_count }}</td>
                        <td>
                            <!-- Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                    <i class="tio-settings"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item"
                                       href="{{route('admin.units.edit',[$unit['id']])}}">{{translate('edit')}}</a>
                                    <a class="dropdown-item" href="javascript:"
                                       onclick="form_alert('units-{{$unit['id']}}','{{translate("Want to delete this")}}')">{{translate('delete')}}</a>
                                    <form action="{{route('admin.units.destroy',[$unit['id']])}}"
                                          method="post" id="units-{{$unit['id']}}">
                                        @csrf @method('delete')
                                    </form>
                                </div>
                            </div>
                            <!-- End Dropdown -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="my-3">
                {{ $units->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

@endsection


@push('script_2')
    <script>
        $(document).ready(function () {
            $("#search").on('search', function (event) {
                if (event.target.value === "") {
                    window.location = "{{ route('admin.units.index') }}"
                }
            });
        })
    </script>
@endpush