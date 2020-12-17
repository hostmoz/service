@extends('service::layouts.app_install', ['title' => __('service::install.admin_setup')])

@section('content')
<div class="single-report-admit">
    <div class="card-header">
        <h2 class="text-center text-uppercase" style="color: whitesmoke">{{ __('service::install.admin_setup') }}
        </h2>

    </div>
</div>

<div class="card-body">
    <div class="requirements">
        <div class="row">

            <div class="col-md-12">
                <form method="post" action="{{ route('service.user') }}" id="content_form">
                    <div class="form-group">
                        <label class="required" for="name">{{ __('service::install.name') }}</label>
                        <input type="text" class="form-control " name="name" id="name"  required="required"  placeholder="{{ __('service::install.name') }}" >
                    </div>

                    <div class="form-group">
                        <label class="required" for="email">{{ __('service::install.email') }}</label>
                        <input type="email" class="form-control" name="email" id="email" required="required" placeholder="{{ __('service::install.email') }}">
                    </div>
                    <div class="form-group">
                        <label class="required" for="username">{{ __('service::install.username') }}</label>
                        <input type="text" class="form-control" name="username" id="username" required="required" placeholder="{{ __('service::install.username') }}">
                    </div>
                    <div class="form-group">
                        <label class="required" for="contact_number">{{ __('service::install.contact_number') }}</label>
                        <input type="text" class="form-control" name="contact_number" id="contact_number" placeholder="{{ __('service::install.contact_number') }}">
                    </div>
                    <div class="form-group">
                        <label class="required" for="password">{{ __('service::install.password') }}</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="{{ __('service::install.password') }}">
                    </div>
                    <div class="form-group">
                        <label class="required" for="password_confirmation">{{ __('service::install.password_confirmation') }}</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{ __('service::install.password_confirmation') }}" data-parsley-equalto="#password">
                    </div>
                   <button type="submit" class="offset-3 col-sm-6 primary-btn fix-gr-bg mt-40 submit" style="background-color: rebeccapurple;color: whitesmoke">{{ __('service::install.ready_to_go') }}</button>
                   <button type="button" class="offset-3 col-sm-6 primary-btn fix-gr-bg mt-40 submitting" disabled style="background-color: rebeccapurple;color: whitesmoke; display:none">{{ __('service::install.submitting') }}</button>
                </form>
            </div>

        </div>
    </div>
</div>
@stop

@push('js')
    <script>
        _formValidation('content_form');
    </script>
@endpush
