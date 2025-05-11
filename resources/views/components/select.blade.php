@props([
    'title'=>'',
    'name'=>'',
    'placeholder'=>'',
    'value'=>'',
    'options'=>[],
    'design'=>'full'
])
<div class="fv-row mb-7 @if($design !='full') d-flex jsutify-content-center align-items-center  @endif relative @error($name) fv-plugins-bootstrap5-row-invalid has_error @enderror">
    @if($title)
    <label class="form-label fs-6 fw-bold  {{$design!='full'?' w-25':'w-100'}} {{$attributes->has('required')?'required':''}}">{{$title}}</label>
    @endif
    <div class="{{$design!='full'?' w-75':'w-100'}}">
        <select  {{$attributes}} id="{{$name}}" class="form-select form-select-solid select-input fw-bolder" style="width: 100%" name="{{$name}}" data-kt-select2="true" data-placeholder="{{$placeholder??$title}}" @if(!$attributes->has('required')) data-allow-clear="true" @endif>
            <option>{{$placeholder??$title}}</option>
            @foreach($options as $id=>$ob)
                <option value="{{isset($ob->id)?$ob->id:$id}}" {{request()->get($name,$value) == (isset($ob->id)?$ob->id:$id)?'selected':''}}>{{isset($ob->name)?$ob->name:$ob}}</option>
            @endforeach
        </select>
        @error($name)
        <div class="fv-plugins-message-container invalid-feedback help-block has-error">
            {{ $message }}
        </div>
        @enderror
        <span class="fs-7 text-muted text-center w-100 json-error"></span>

    </div>

</div>
