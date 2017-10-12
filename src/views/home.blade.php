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
                                <textarea style="display:none;" id="nestable-output" type="hidden"></textarea>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(count($new_packages) > 0)
        <div class="col-lg-6"> 
            <div class="card">
                <!-- Tab panes -->
                <div class="tab-content">
                    <!--second tab-->
                    <div class="card-block">
                        <form action = "{{ route('AdminMenuAdd') }}" method = "POST">
                            <select class="col-12" id="role" name="selected_package[]" multiple size="10">
                                @foreach($new_packages as $package)
                                    <option value = "{{ $package->package }}:{{ $package->name }}:{{ $package->icon }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                            <button type = "submit" class = "btn btn-success btn-block">Добавить</button>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection