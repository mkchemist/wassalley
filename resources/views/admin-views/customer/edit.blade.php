@extends('layouts.admin.app')

@section('title', translate('Customer List'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="page-header">
        <h1> <span class="tio-edit"></span> {{ __("messages.edit") }} {{ $user->name }}</h1>
    </div>
    <div>
        <form action="{{ route('admin.customer.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            {{-- User name --}}
            <div class="row mx-auto mb-3">
                <div class="col-md-6">
                    <label for="f_name">{{ __('messages.f_name') }}</label>
                    <input type="text" name="f_name" id="f_name" class="form-control" placeholder="" value="{{
                        $user->f_name }}">
                </div>
                <div class="col-md-6">
                    <label for="l_name">{{ __('messages.l_name') }}</label>
                    <input type="text" name="l_name" id="l_name" class="form-control" placeholder="" value="{{
                        $user->l_name }}">
                </div>
            </div>
            {{-- End user name --}}
            {{-- User email & phone --}}
            <div class="row mx-auto mb-3">
                <div class="col-md-6">
                    <label for="email">{{ __('messages.email') }}</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="" value="{{ $user->email }}">
                </div>
                <div class="col-md-6">
                    <label for="phone">{{ __('messages.phone') }}</label>
                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="" value="{{ $user->phone }}">
                </div>
            </div>
            {{-- End user email & phone --}}
            {{-- password and password confirmation --}}
            <div class="row mx-auto">
                <div class="col-md-6">
                    <label for="password">{{ __('messages.password') }}</label>
                    <input type="text" name="password" id="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation">{{ translate("Password") }} {{ translate("confirmation") }}</label>
                    <input type="text" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
            </div>
            {{-- end password and password confirmation --}}
            <hr>
            <div class="form-group">
                <button class="btn btn-primary">{{ translate("save") }}</button>
                <a href="{{ url()->previous() }}" class="btn btn-dark">{{ translate("Back") }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script_2')

@endpush
