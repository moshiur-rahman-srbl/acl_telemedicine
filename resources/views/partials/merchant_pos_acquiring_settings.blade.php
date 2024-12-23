<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <h4 class="text-center">{{ __('Merchant Pos Acquiring Setting') }}</h4>
        </div>
        <div class="card-body">
                <div class="table-responsive">
                    <table id="editable2" class="table table-bordered table-hover font13 not-datatables">
                        <thead class="thead-default">
                        <tr>
                            <th>{{__('Pos')}}</th>
                            <th>{{__('Client ID')}}</th>
                            <th>{{__('Store Key')}}</th>
                            <th>{{__('Terminal ID')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($merchant_pos_acquiring_settings as $merchant_pos_acquiring_setting)
                            @php
                            $pos = $pos->where('id', $merchant_pos_acquiring_setting->pos_id)->first();
                            @endphp
                            <tr>
                                <td> {{ !empty($pos) ? $pos->name.'('.$merchant_pos_acquiring_setting->pos_id.')' : ''  }}</td>
                                <td> {{ $merchant_pos_acquiring_setting->client_id }}</td>
                                <td> {{ $merchant_pos_acquiring_setting->store_key }}</td>
                                <td> {{ $merchant_pos_acquiring_setting->terminal_id }}</td>
                                <td class="d-flex justify-content-center">
                                    <a data-id="{{$merchant_pos_acquiring_setting->id}}"
                                       data-pos_id_text="{{ !empty($pos) ? $pos->name.'('.$merchant_pos_acquiring_setting->pos_id.')' : ''  }}"
                                       data-pos_id="{{$merchant_pos_acquiring_setting->pos_id}}"
                                       data-client_id="{{$merchant_pos_acquiring_setting->client_id}}"
                                       data-store_key="{{$merchant_pos_acquiring_setting->store_key}}"
                                       data-terminal_id="{{$merchant_pos_acquiring_setting->terminal_id}}"
                                       href="#"
                                       title="Edit"
                                       class="merchant_pos_acquiring_setting_edit text-muted font-16 mr-1 ml-1 float-left"><i class="ti-pencil-alt"></i>
                                    </a>

                                    <form action="" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        <input type="hidden" name="action" value="merchant_pos_acquiring_setting">
                                        <input type="hidden" name="merchant_pos_acquiring_setting" value="delete">
                                        <input type="hidden" id="pos_acquiring_setting_id" name="merchant_pos_acquiring_setting_id" value="{{ $merchant_pos_acquiring_setting->id }}">
                                        <button style="border: 0px; background: transparent" type="submit" class="text-muted font-16 ml-1"><i class="ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-center" colspan="14">{{__('No data found')}}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            <form  action="#" method="POST">
                @csrf
                <input type="hidden" name="action" value="merchant_pos_acquiring_setting">
                <input type="hidden" name="merchant_pos_acquiring_setting" value="add">

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-hover font13 not-datatables" id="myTable">
                        <thead>
                            <tr>
                                <th>{{ __('Pos') }}</th>
                                <th>{{ __('Client ID') }}</th>
                                <th>{{ __('Store Key') }}</th>
                                <th>{{ __('Terminal ID') }}</th>
                                <th>
                                    <a href="javascript:void(0);" id="pos_acquiring_add_button" class="ml-3 btn btn-sm btn-primary pull-right">
                                    <i class="fa fa-plus-circle"></i>
                                        {{__('Add')}}
                                    </a>
                                </th>
                            </tr>
                        </thead >
                        <tbody class="pos_acquiring_field_wrapper" id="pos_acquiring_field_wrapper">

                        </tbody>
                    </table>
                </div>

                <div class="col-md-12 mt-4">
                    <button type="submit" class="btn btn-primary btn-block text-uppercase">{{__('Save') }}</button>
                </div>

            </form>
        </div>

    </div>
</div>

<div class="modal fade" id="merchant_pos_acquiring_setting_edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{__('Update Merchant Pos Acquiring Setting')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>

            <div class="modal-body">
                <form action="" method="POST" >
                    @csrf
                    <input type="hidden" id="merchant_pos_acquiring_setting_edit_id" name="merchant_pos_acquiring_setting_id">
                    <input type="hidden" id="action" name="action" value="merchant_pos_acquiring_setting">
                    <input type="hidden" id="action" name="merchant_pos_acquiring_setting" value="update">
                    <div class="form-group">
                        <label for="merchant_pos_acquiring_setting_pos_id" class="col-form-label">Pos:</label>
                        <select name="pos_acquiring_pos_ids[]" id="merchant_pos_acquiring_setting_pos_id" class="form-control merchant_pos_acquiring_setting_pos_id" required>
                            <option  value="" disabled="true" selected="true"> {{__('Please select')}}</option>
                            @foreach($merchant_pos_acquiring_settings_pos_list as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}({{ $pos->id}})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="merchant_pos_acquiring_setting_client_id" class="col-form-label">{{__('Client ID')}}</label>
                        <input type="text" name="client_ids[]" id="merchant_pos_acquiring_setting_client_id" class="form-control" placeholder="Client ID" required>
                    </div>

                    <div class="form-group">
                        <label for="merchant_pos_acquiring_setting_store_key" class="col-form-label">{{__('Store Key')}}</label>
                        <input type="text" name="store_keys[]" id="merchant_pos_acquiring_setting_store_key" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="merchant_pos_acquiring_setting_terminal_id" class="col-form-label">{{__('Terminal ID')}}</label>
                        <input type="text" name="terminal_ids[]" id="merchant_pos_acquiring_setting_terminal_id" class="form-control" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{__('Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

