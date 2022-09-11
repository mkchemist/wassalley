@extends('layouts.admin.app')


@section('title', 'Admin | Edit unit')

@section('content')
<div>
    <div class="container">
        {{-- Units Form --}}
        <form action="{{ route('admin.units.update', $unit->id) }}" method="POST" class="p-lg-3 p-2 border rounded mt-3">
            @csrf
            @method("PUT")
            <h4>Update {{ $unit->name }}</h4>
            <div class="row mx-auto form-group">
                <div class="col-lg-6">
                    <label for="name">{{ __('messages.name') }}</label>
                    <input type="text" name="name" id="name" class="form-control form-control-sm @error('name') border border-danger @enderror" placeholder="Unit name" value="{{ $unit->name }}">
                    @error('name')
                        <span class="text-danger small">{{ implode(', ', $errors->get('name')) }}</span>
                    @enderror
                </div>
                <div class="col-lg-6">
                    <label for="name">Minimum quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control form-control-sm @error('quantity') border border-danger @enderror" value="{{ $unit->quantity }}" min="0.1" step="0.005">
                    @error('quantity')
                    <span class="text-danger small">{{ implode(', ', $errors->get('quantity')) }}</span>
                @enderror
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary">{{ __("messages.Update") }}</button>
            </div>
        </form>
    </div>
</div>
@endsection