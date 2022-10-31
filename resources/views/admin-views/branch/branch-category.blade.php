@extends('layouts.admin.app')

@section('title', "Branch Categories")




@section('content')
<div class="p-2">
  <div class="card container-fluid">
    <div class="card-header">
      <h2>Branch Categories</h2>
    </div>
    <div class="card-body">
      <div class="p-lg-3 p-2">
        {{-- Branch selector --}}
        <div class="form-group">
          <label for="">{{ translate("branch") }}</label>
          <select name="branch" id="branch" class="form-control">
            <option value="">Select Branch</option>
            @foreach ($branches as $branch)
              <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
          </select>
        </div>
        {{-- end branch selector --}}
        <div class="form-group">
          <button class="btn btn-primary" id="show_branch_btn">{{ translate('show') }}</button>
        </div>
      </div>
      <hr>
      <div class="p-lg-3 p-2" style="min-height:500px">
        <div id="branch_content">

        </div>
      </div>
    </div>
  </div>
</div>

<template id="idle_template">
  <div class="text-center py-5">
    <i class="tio-checkmark-circle tio-xl text-primary"></i>
    <h4>{{ translate("you_must_select_branch") }}</h4>
  </div>
</template>
@endsection


@push('script')
  <script src="{{ asset('public/assets/admin/js/jquery.min.js') }}"></script>
  <script src="{{ asset('public/assets/admin/js/sweet_alert.js') }}"></script>
  <script>
    $(document).ready(function () {
      $("#branch_content").html($("#idle_template").html())

      var ajax_url = "{{ route('admin.branch-categories.show') }}";

      $("#show_branch_btn").click(function () {
        var branch = $("#branch").val();
        if (branch) {
          fetch(`${ajax_url}?branch=${branch}`)
          .then((res) => res.json())
          .then((res) => {
            $("#branch_content").html(res.view);
          }).catch((err) => {
            console.log(err);
          })
        } else {
          Swal.fire({
            title: "{{ translate('you_must_select_branch') }}",
            type: "info",
          })
        }
      });

    });
  </script>
@endpush
