@extends('adminamazing::teamplate')

@section('pageTitle', 'Редактирование меню')
@section('content')
    <div class="row">
        <!-- Column -->
        <div class="col-lg-6"> 
            <div class="card">
                <!-- Tab panes -->
                <div class="tab-content">                   
                    <!--second tab-->
                    <div class="card-block">
                        <div class="myadmin-dd-empty dd" id="nestable2">
                            <ol class="dd-list">
                                {!!$tree!!}
                                <div class="modal fade" id="editModal" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('AdminMenuUpdate', 'title') }}" method="POST" class="form-horizontal">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="title">Название раздела</label>
                                                        <input type="text" class="form-control" name="title" id="title">
                                                        <label for="icon">Иконка раздела</label>
                                                        <input type="text" class="form-control" name="icon" id="icon">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    {{ method_field('PUT') }}
                                                    <input type="hidden" name="id" value="">
                                                    <button type="submit" class="btn btn-success">Изменить</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="deleteModal" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('AdminMenuDelete') }}" method="POST" class="form-horizontal">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                </div>
                                                <div class="modal-body">
                                                    Вы точно хотите удалить данный раздел со всеми его подпунктами?
                                                </div>
                                                <div class="modal-footer">
                                                    {{ method_field('DELETE') }}
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="id" value="">
                                                    <button type="submit" class="btn btn-danger">Удалить</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <textarea style="display:none;" id="nestable-output" type="hidden"></textarea>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(count($new_packages) > 0)
        <div class="col-lg-3"> 
            <div class="card">
                <!-- Tab panes -->
                <div class="tab-content">
                    <!--second tab-->
                    <div class="card-block">
                        <form action = "{{ route('AdminMenuAdd', 'package') }}" method = "POST" class="form-horizontal">
                            <select class="form-control col-12" id="role" name="selected_package[]" multiple size="{{ count($new_packages+$dev_packages) }}">
                                @foreach($new_packages as $package)
                                    <option value="{{ $package->package }}:{{ $package->name }}:{{ $package->icon }}">{{ $package->name }}</option>
                                @endforeach
                                @foreach($dev_packages as $package)
                                    <option value="{{ $package->package }}:{{ $package->name }}:{{ $package->icon }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-success btn-block">Добавить</button>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-lg-3"> 
            <div class="card">
                <!-- Tab panes -->
                <div class="tab-content">
                    <!--second tab-->
                    <div class="card-block">
                        <form action="{{route('AdminMenuAdd', 'stub')}}" method="POST" class="form-horizontal">          
                            <div class="form-group">
                                <label for="subject">Название раздела</label>
                                <input type="text" class="form-control" name="title" id="title">
                            </div>
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-success btn-block">Создать</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection