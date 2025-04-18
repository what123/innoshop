@extends('panel::layouts.app')
@section('body-class', '')

@section('title', __('panel/menu.catalogs'))
@section('page-title-right')
  <a href="{{ panel_route('catalogs.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('panel/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

    <x-panel-data-criteria :criteria="$criteria ?? []" :action="panel_route('catalogs.index')" />

      @if ($catalogs->count())
      <div class="table-responsive">
        <table class="table align-middle">
        <thead>
        <tr>
        <td>{{ __('panel/common.id')}}</td>
        <td>{{ __('panel/catalog.title') }}</td>
        <td>{{ __('panel/catalog.parent') }}</td>
        <td>{{ __('panel/common.slug') }}</td>
        <td>{{ __('panel/common.position') }}</td>
        <td>{{ __('panel/common.active') }}</td>
        <td>{{ __('panel/common.actions') }}</td>
        </tr>
        </thead>
        <tbody>
        @foreach($catalogs as $item)
        <tr>
        <td>{{ $item->id }}</td>
        <td>{{ $item->fallbackName('title') }}</td>
        <td>{{ $item->parent ? $item->parent->fallbackName('title') : '-' }}</td>
        <td>{{ $item->slug }}</td>
        <td>{{ $item->position }}</td>
        <td>@include('panel::shared.list_switch', ['value' => $item->active, 'url' => panel_route('catalogs.active', $item->id)])</td>
        <td>
         <div class="d-flex gap-1">
        <a href="{{ panel_route('catalogs.edit', [$item->id]) }}">
        <el-button size="small" plain type="primary">{{ __('panel/common.edit')}}</el-button>
        </a>
        <form ref="deleteForm" action="{{ panel_route('catalogs.destroy', [$item->id]) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{ __('panel/common.delete')}}</el-button>
        </form>
        </div>
        </td>
        </tr>
      @endforeach
        </tbody>
        </table>
      </div>
      {{ $catalogs->withQueryString()->links('panel::vendor/pagination/bootstrap-4') }}
    @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
    <script>
     const { createApp, ref } = Vue;
    const { ElMessageBox, ElMessage } = ElementPlus;

    const app = createApp({
    setup() {
    const deleteForm = ref(null);

    const open = (itemId) => {
     ElMessageBox.confirm(
      '{{ __("common/base.hint_delete") }}',
      '{{ __("common/base.cancel") }}',
      {
        confirmButtonText: '{{ __("common/base.confirm")}}',
        cancelButtonText: '{{ __("common/base.cancel")}}',
        type: 'warning',
      }
      )
      .then(() => {
       const deleteUrl = urls.base_url + '/catalogs/' + itemId;
       deleteForm.value.action = deleteUrl;
       deleteForm.value.submit();
      })
      .catch(() => {
      // 取消删除
      });
    };

    return { open, deleteForm };
    }
    });

    app.use(ElementPlus);
    app.mount('#app');
    </script>
@endpush
