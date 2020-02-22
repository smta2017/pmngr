@extends('layouts.app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ __($pageTitle) }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ __($pageTitle) }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')

<style>
    .col-in {
        padding: 0 20px !important;

    }

    .fc-event{
        font-size: 10px !important;
    }

    @media (min-width: 769px) {
        #wrapper .panel-wrapper{
            height: 250px;
            overflow-y: auto;
        }
    }

</style>
@endpush

@section('content')

    <div class="row">
        @foreach($faqCategories as $faqCategory)


            <div class="col-md-4">
                <div class="panel panel-inverse">
                    <div class="panel-heading"> {{ $faqCategory->name }}</div>
                    <div class="panel-wrapper collapse in">
                        <div class="panel-body">
                            <ul class="list-icons">
                                @foreach($faqCategory->faqs as $faq)
                                    <li>
                                        <a href="javascript:void(0)" onclick="showFaqDetails({{$faq->id}})">
                                            <i class="fa fa-file-text"></i> {{ $faq->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{--Ajax Modal--}}
    <div class="modal fade bs-modal-md in" id="faqDetailsModal" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg" id="faq-details-modal-data-application">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    {{--Ajax Modal Ends--}}
@endsection


@push('footer-script')
<script>
    function showFaqDetails(id) {
        var url = '{{ route('admin.faqs.details', ':id')}}';
        url = url.replace(':id', id);

        $.ajaxModal('#faqDetailsModal', url);
    }
</script>
@endpush

