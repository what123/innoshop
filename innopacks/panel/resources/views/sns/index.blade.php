@push('header')
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="{{ asset('vendor/vue/3.5/vue.global.prod.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.1/dist/clipboard.min.js"></script>
@endpush

@extends('panel::layouts.app')

@section('title', __('panel/menu.sns'))

<x-panel::form.right-btns />

@section('content')
<div id="app" class="card h-min-600">
  <div class="card-header no-background">
    <h5 class="card-title mb-0">三方登录配置</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="sns-form" action="{{ panel_route('sns.index') }}" method="POST">
      @csrf

      <div class="container mt-1">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th scope="col">类型</th>
              <th scope="col">状态</th>
              <th scope="col">Client Secret</th>
              <th scope="col">回调地址</th>
              <th scope="col">排序</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index">
              <th scope="row" class="bg-white">
                <div class="dropdown">
                  <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                    id="sns-type-button">@{{ item.snsType }}</button>
                  <ul class="dropdown-menu" id="sns-type-menu">
                    <li v-for="option in availableOptions" :key="option">
                      <a class="dropdown-item" :data-value="option" @click="handleDropdownClick(item, $event)">@{{
                        option }}</a>
                    </li>
                  </ul>
                </div>
                <input type="hidden" :name="'sns_type[' + index + ']'" :value="item . snsType">
              </th>
              <td class="align-middle">
                <label class="switch">
                  <input type="checkbox" v-model="item.status">
                  <span class="slider round"></span>
                </label>
              </td>
              <td><input type="text" class="form-control" :name="'client_secret[' + index + ']'"
                  v-model="item.clientSecret"></td>
              <td>
                <div class="input-group">
                  <input type="text" class="form-control" :name="'callback_url[' + index + ']'"
                    v-model="item.callbackUrl" :ref="'callbackUrlInput' + index">
                  <button class="btn btn-light copy-button" @click.prevent="copyCallbackUrl(index)" title="复制">
                    <i class="bi bi-back" style="font-size: 16px;"></i>
                  </button>
                </div>
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <button class="btn btn-light" @click.prevent="moveUp(index)" :disabled="index === 0" title="上移">
                    <i class="bi bi-arrow-left" style="font-size: 16px;"></i>
                  </button>
                  <span class="mx-2">@{{ item.sortOrder }}</span>
                  <button class="btn btn-light" @click.prevent="moveDown(index)" :disabled="index === items . length - 1"
                    title="下移">
                    <i class="bi bi-arrow-right" style="font-size: 16px;"></i>
                  </button>
                </div>
              </td>
              <td class="align-middle text-center">
                <template v-if="isFormComplete(item)">
                  <i class="bi bi-x-square fs-4" @click="deleteRow(index)"></i>
                  <i class="bi bi-plus-square fs-4 ml-2" @click="addAfter(index)"
                    v-if="index === items.length - 1 && items.length < 3"></i>
                </template>
              </td>
            </tr>
          </tbody>
          <tfoot v-if="items.length === 0">
            <tr>
              <td colspan="6" style="text-align: center;">
                <div class="no-data-container">
                  暂无数据～
                  <button @click="add" type="button" class="btn btn-primary">添加</button>
                </div>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
      <button type="submit" class="d-none"></button>
    </form>
  </div>
  <button @click="printFormData">打印表单数据</button>
</div>

@endsection

@push('footer')
  <style>
    .switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
    }

    .switch input {
    opacity: 0;
    width: 0;
    height: 0;
    }

    .switch .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #333;
    transition: 0.4s;
    }

    .switch .slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: 0.4s;
    }

    .switch input:checked+.slider {
    background-color: #2196F3;
    }

    .switch input:focus+.slider {
    box-shadow: 0 0 1px #2196F3;
    }

    .switch input:checked+.slider:before {
    transform: translateX(20px);
    }

    .switch .slider.round {
    border-radius: 20px;
    }

    .switch .slider.round:before {
    border-radius: 50%;
    }
  </style>
  <script>
    const {
    createApp,
    ref,
    computed,
    onMounted,
    nextTick
    } = Vue;

    createApp({
    setup() {
      const items = ref([]);

      const allOptions = ['Facebook', 'Twitter', 'Google'];

      const availableOptions = computed(() => {
      const usedOptions = items.value.map(item => item.snsType);
      return allOptions.filter(option => !usedOptions.includes(option));
      });

      const handleDropdownClick = (item, e) => {
      e.preventDefault();
      const selectedValue = e.target.getAttribute('data-value');
      item.snsType = selectedValue;
      document.querySelectorAll('#sns-type-menu .dropdown-item').forEach(item => {
        item.classList.remove('active');
      });
      e.target.classList.add('active');
      };

      const copyCallbackUrl = async (index) => {
      const input = document.querySelector(`#app input[name='callback_url[${index}]']`);
      if (!input) {
        console.error('Input element not found');
        return;
      }
      const textToCopy = input.value;

      try {
        if (navigator.clipboard) {
        await navigator.clipboard.writeText(textToCopy);
        } else {
        const textarea = document.createElement('textarea');
        textarea.value = textToCopy;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        }
      } catch (error) {
        console.error('Failed to copy text: ', error);
      }
      };

      const add = () => {
      if (items.value.length < 3) {
        items.value.push({
        snsType: availableOptions.value[0] || 'Facebook',
        status: false,
        clientSecret: '',
        callbackUrl: '',
        sortOrder: items.value.length + 1
        });
      }
      };

      const addAfter = (index) => {
      if (items.value.length < 3) {
        items.value.splice(index + 1, 0, {
        snsType: availableOptions.value[0] || 'Facebook',
        status: false,
        clientSecret: '',
        callbackUrl: '',
        sortOrder: items.value.length + 1
        });
      }
      };

      const deleteRow = (index) => {
      items.value.splice(index, 1);
      // 重新计算排序号
      items.value.forEach((item, newIndex) => {
        item.sortOrder = newIndex + 1;
      });
      };

      const isFormComplete = (item) => {
      return item.snsType && item.status !== null && item.clientSecret && item.callbackUrl && item.sortOrder;
      };

      const moveUp = (index) => {
      if (index > 0) {
        [items.value[index], items.value[index - 1]] = [items.value[index - 1], items.value[index]];
        items.value = [...items.value]; // Trigger reactivity
      }
      };

      const moveDown = (index) => {
      if (index < items.value.length - 1) {
        [items.value[index], items.value[index + 1]] = [items.value[index + 1], items.value[index]];
        items.value = [...items.value]; // Trigger reactivity
      }
      };

      const printFormData = () => {
      const formData = items.value.map(item => ({
        snsType: item.snsType,
        status: item.status ? '启用' : '禁用',
        clientSecret: item.clientSecret,
        callbackUrl: item.callbackUrl,
        sortOrder: item.sortOrder
      }));
      console.log(formData);
      };

      onMounted(() => {
      items.value.forEach((item, index) => {
        const input = document.querySelector(`#app input[name='callback_url[${index}]']`);
        if (input) {
        item.callbackUrlInput = input;
        }
      });
      });

      return {
      items,
      add,
      handleDropdownClick,
      copyCallbackUrl,
      deleteRow,
      isFormComplete,
      addAfter,
      availableOptions,
      moveUp,
      moveDown,
      printFormData
      };
    }
    }).mount('#app');
  </script>
@endpush