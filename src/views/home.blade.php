@extends('adminamazing::teamplate')

@section('pageTitle', trans('translate-menu::menu.editingMenu'))
@section('content')
    <div class="row">
        <div class="col-lg-6"> 
            <div class="card">
                <div class="tab-content">
                    <div class="card-block">
                        <div class="myadmin-dd-empty dd" id="nestable2">
                            <ol class="dd-list">{!!$tree!!}</ol>
                            <div class="modal fade" id="editModal" aria-hidden="true" style="display: none;">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('AdminMenuUpdate', 'category') }}" method="POST" class="form-horizontal">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="title">{{ trans('translate-menu::menu.sectionName') }}</label>
                                                    <input type="text" class="form-control" name="title" id="title">
                                                    <label for="icon">{{ trans('translate-menu::menu.sectionIcon') }}</label>
                                                    <input type="text" class="form-control" name="icon" id="icon">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                {{ method_field('PUT') }}
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id" value="">
                                                <button type="submit" class="btn btn-success">{{ trans('translate-menu::menu.update') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <textarea style="display:none;" id="nestable-output" type="hidden"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(count($new_packages) > 0)
        <div class="col-lg-3"> 
            <div class="card">
                <div class="tab-content">
                    <div class="card-block">
                        <form action = "{{ route('AdminMenuCreate', 'package') }}" method = "POST" class="form-horizontal">
                            <select class="form-control col-12" id="role" name="selected_package[]" multiple size="{{ count($new_packages+$dev_packages) }}">
                                @foreach($new_packages as $package)
                                    <option value="{{ $package->package }}:{{ $package->name }}:{{ $package->icon }}">{{ $package->name }}</option>
                                @endforeach
                                @if(count($dev_packages) > 0)
                                    @foreach($dev_packages as $package)
                                        <option value="{{ $package->package }}:{{ $package->name }}:{{ $package->icon }}">{{ $package->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-success btn-block">{{ trans('translate-menu::menu.add') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-lg-3"> 
            <div class="card">
                <div class="tab-content">
                    <div class="card-block">
                        <form action="{{route('AdminMenuCreate', 'stub')}}" method="POST" class="form-horizontal">          
                            <div class="form-group">
                                <label for="title">{{ trans('translate-menu::menu.sectionName') }}</label>
                                <input type="text" class="form-control" name="title" id="title">
                            </div>
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-success btn-block">{{ trans('translate-menu::menu.create') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/adminamazing/assets/plugins/nestable/jquery.nestable.js') }}"></script>
        <script>
            var route = '{{ route('AdminMenuDelete') }}';
            message = '{{ trans('translate-menu::menu.deleteConfirm') }}';
            
            $('.edit_toggle').on('click', function(e){
                var menu = jQuery.parseJSON( $(this).attr('data-rel') );
                $('#editModal').find('input[name=title]').val(menu.title);
                $('#editModal').find('input[name=icon]').val(menu.icon);
                $('#editModal').find('input[type=hidden][name=id]').val(menu.id);
            });
            
            var updateOutput = function(e) {
                var list = e.length ? e : $(e.target),
                    output = list.data('output');

                $.ajax({
                        url: '{{route('AdminMenuUpdate', 'tree')}}',
                        method: 'PUT',
                        data: {
                            tree: list.nestable('serialize')
                        },
                        headers: {
                            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            console.log(res);
                        }
                });

                if (window.JSON) {           
                    output.val(window.JSON.stringify(list.nestable('serialize'))); //, null, 2));
                } else {
                    output.val('JSON browser support required for this demo.');
                }
            };

            $('#nestable2').nestable({
                group: 1
            }).on('change', updateOutput);

            updateOutput($('#nestable2').data('output', $('#nestable-output')));

            $('#nestable-menu').on('click', function(e) {
                var target = $(e.target),
                    action = target.data('action');

                if (action === 'expand-all') {
                    $('.dd').nestable('expandAll');
                }
                if (action === 'collapse-all') {
                    $('.dd').nestable('collapseAll');
                }
            });

            $('#nestable-menu').nestable();
        </script>
    @endpush
@endsection