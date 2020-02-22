<style>
    .stripe-button-el{
        display: none;
    }
    .displayNone {
        display: none;
    }
    .checkbox-inline, .radio-inline {
        vertical-align: top !important;
    }
    .payment-type {
        border: 1px solid #e1e1e1;
        padding: 20px;
        background-color: #f3f3f3;
        border-radius: 10px;

    }
    .box-height {
        height: 78px;
    }
    .button-center{
        display: flex;
        justify-content: center;
    }
</style>
<div id="event-detail">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><i class="fa fa-cash"></i> Choose Payment Method</h4>
    </div>
    <div class="modal-body">
        <div class="form-body">
            <div class="row">
                <div class="col-12 col-sm-12 mt-40 text-center">
                    <div class="form-group">
                        <div class="radio-list">
                            @if(($stripeSettings->paypal_status == 'active' || $stripeSettings->stripe_status == 'active'))
                                <label class="radio-inline p-0">
                                    <div class="radio radio-info">
                                        <input checked onchange="showButton('online')" type="radio" name="method" id="radio13" value="high">
                                        <label for="radio13">@lang('modules.client.online')</label>
                                    </div>
                                </label>
                            @endif
                            @if($methods->count() > 0)
                                <label class="radio-inline">
                                    <div class="radio radio-info">
                                        <input type="radio" @if((!($stripeSettings->paypal_status == 'active') && !($stripeSettings->stripe_status == 'active'))) checked @endif onchange="showButton('offline')" name="method" id="radio15">
                                        <label for="radio15">@lang('modules.client.offline')</label>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-12 mt-40 text-center" id="onlineBox">
                    @if(($stripeSettings->paypal_status == 'active' || $stripeSettings->stripe_status == 'active'))
                        <div class="form-group payment-type box-height">
                            @if($stripeSettings->paypal_client_id != null && $stripeSettings->paypal_secret != null && $stripeSettings->paypal_status == 'active')
                                <button type="submit" class="btn btn-warning waves-effect waves-light paypalPayment pull-left" data-toggle="tooltip" data-placement="top" title="Choose Plan">
                                    <i class="icon-anchor display-small"></i><span>
                                    <i class="fa fa-paypal"></i> @lang('modules.invoices.payPaypal')</span>
                                </button>
                            @endif
                            @if($stripeSettings->razorpay_key != null && $stripeSettings->razorpay_secret != null  && $stripeSettings->razorpay_status == 'active')
                                <button type="submit" class="btn btn-info waves-effect waves-light pull-left m-l-10" onclick="razorpaySubscription();" data-toggle="tooltip" data-placement="top" title="Choose Plan">
                                    <i class="icon-anchor display-small"></i><span>
                                        <i class="fa fa-credit-card-alt"></i> RazorPay </span>
                                </button>
                            @endif
                            @if($stripeSettings->api_key != null && $stripeSettings->api_secret != null  && $stripeSettings->stripe_status == 'active')
                                <div class="m-l-10">
                                    <form action="{{ route('admin.payments.stripe') }}" method="POST">
                                        <input type="hidden" name="plan_id" value="{{ $package->id }}">
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        {{ csrf_field() }}
                                        <script
                                                src="https://checkout.stripe.com/checkout.js"
                                                class="stripe-button d-flex flex-wrap justify-content-between align-items-center"
                                                data-email="{{ $company->company_email }}"
                                                data-key="{{ config('services.stripe.key') }}"
                                                @if($type == 'annual')
                                                    data-amount="{{ round($package->annual_price) * 100 }}"
                                                @else
                                                    data-amount="{{ round($package->monthly_price) * 100 }}"
                                                @endif
                                                data-button-name="Choose Plan"
                                                data-description="Payment through debit card."
                                                data-image="{{ $logo }}"
                                                data-locale="auto"
                                                data-currency="{{ $superadmin->currency->currency_code }}">
                                        </script>

                                        <button type="submit" class="btn btn-success waves-effect waves-light" data-toggle="tooltip" data-placement="top" title="Choose Plan">
                                            <i class="icon-anchor display-small"></i><span>
                                        <i class="fa fa-cc-stripe"></i> @lang('modules.invoices.payStripe')</span></button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-12 col-sm-12 mt-40 text-center">
                    @if($methods->count() > 0)
                        <div class="form-group @if(($stripeSettings->paypal_status == 'active' || $stripeSettings->stripe_status == 'active')) displayNone @endif payment-type" id="offlineBox">
                            <div class="radio-list">
                                @forelse($methods as $key => $method)
                                    <label class="radio-inline @if($key == 0) p-0 @endif">
                                        <div class="radio radio-info" >
                                            <input @if($key == 0) checked @endif onchange="showDetail('{{ $method->id }}')" type="radio" name="offlineMethod" id="offline{{$key}}"
                                                   value="{{ $method->id }}">
                                            <label for="offline{{$key}}" class="text-info" >
                                                {{ ucfirst($method->name) }} </label>
                                        </div>
                                        <div class="" id="method-desc-{{ $method->id }}">
                                            {!! $method->description !!}
                                        </div>
                                    </label>
                                @empty
                                @endforelse
                            </div>
                            <div class="row">
                                <div class="col-md-12 " id="methodDetail">
                                </div>

                                @if(count($methods) > 0)
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-info save-offline" onclick="selectOffline('{{ $package->id }}')">@lang('app.select')</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">Close</button>
    </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script src="{{ asset('pricing/js/index.js') }}"></script>
<script>
    // Payment mode
    function showButton(type){

        if(type == 'online'){
            $('#offlineBox').addClass('displayNone');
            $('#onlineBox').removeClass('displayNone')();
        }else{
            $('#offlineBox').removeClass('displayNone');
            $('#onlineBox').addClass('displayNone')();
        }
    }
    // redirect on paypal payment page
    $('body').on('click', '.paypalPayment', function(){
        $.easyBlockUI('#package-select-form', 'Redirecting Please Wait...');
        var url = "{{ route('admin.paypal', [$package->id, $type]) }}";
        window.location.href = url;
    });


    function selectOffline(package_id) {
        let offlineId = $("input[name=offlineMethod]").val();
        $.ajaxModal('#package-offline', '{{ route('admin.billing.offline-payment')}}'+'?package_id='+package_id+'&offlineId='+offlineId+'&type='+'{{ $type }}');
        {{--$.easyAjax({--}}
        {{--    url: '{{ route('admin.billing.offline-payment') }}',--}}
        {{--    type: "POST",--}}
        {{--    redirect: true,--}}
        {{--    data: {--}}
        {{--        package_id: package_id,--}}
        {{--        "offlineId": offlineId--}}
        {{--    }--}}
        {{--})--}}
    }
    {{--$('.save-offline').click(function() {--}}
    {{--    let offlineId = $("input[name=offlineMethod]").val();--}}

    {{--    $.easyAjax({--}}
    {{--        url: '{{ route('client.invoices.store') }}',--}}
    {{--        type: "POST",--}}
    {{--        redirect: true,--}}
    {{--        data: {invoiceId: "{{ $invoice->id }}", "_token" : "{{ csrf_token() }}", "offlineId": offlineId}--}}
    {{--    })--}}

    {{--})--}}


    //Confirmation after transaction
    function razorpaySubscription() {
        var plan_id = '{{ $package->id }}';
        var type = '{{ $type }}';
        $.easyAjax({
            type:'POST',
            url:'{{route('admin.billing.razorpay-subscription')}}',
            data: {plan_id: plan_id,type: type,_token:'{{csrf_token()}}'},
            success:function(response){
                razorpayPaymentCheckout(response.subscriprion)
           }
        })
    }


    function razorpayPaymentCheckout(subscriptionID) {
        var options = {
            "key": "{{ $stripeSettings->razorpay_key }}",
            "subscription_id":subscriptionID,
            "name": "{{$companyName}}",
            "description": "{{ $package->description }}",
            "image": "{{ $logo }}",
            "handler": function (response){
                confirmRazorpayPayment(response);
            },
            "notes": {
                "package_id": '{{ $package->id }}',
                "package_type": '{{ $type }}',
                "company_id": '{{ $company->id }}'
            },
        };

        var rzp1 = new Razorpay(options);
        rzp1.open();
    }

    //Confirmation after transaction
    function confirmRazorpayPayment(response) {
        var plan_id = '{{ $package->id }}';
        var type = '{{ $type }}';
         var payment_id = response.razorpay_payment_id;
         var subscription_id = response.razorpay_subscription_id;
         var razorpay_signature = response.razorpay_signature;
//         console.log([plan_id, type, payment_id, subscription_id, razorpay_signature]);
        $.easyAjax({
            type:'POST',
            url:'{{route('admin.billing.razorpay-payment')}}',
            data: {paymentId: payment_id,plan_id: plan_id,subscription_id: subscription_id,type: type,razorpay_signature: razorpay_signature,_token:'{{csrf_token()}}'},
            redirect:true,
        })
    }
</script>

