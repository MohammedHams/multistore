@props(['disabled' => false,'title'=>'','name'=>'','hint'=>'','type'=>'text','design'=>'full'])

<div class="fv-row mb-7 @if($design !='full') d-flex flex-wrap jsutify-content-center align-items-center  @endif relative @error($name) fv-plugins-bootstrap5-row-invalid has_error @enderror">
    <!--begin::Label-->
    <label class="{{$attributes->has('required')?'required':''}} fw-bold fs-6 mb-2 {{$design!='full'?' w-25':''}}">{{$title}}</label>
    <!--end::Label-->
    <!--begin::Input-->
{{--    @if($type=='date') data="has_date_picker" @endif--}}
    <input  {{ $disabled ? 'disabled' : '' }} type="{{$type}}" {!! $attributes->merge(['class' => 'form-control form-control-solid mb-3 mb-lg-0'.($design!='full'?' w-75':'')]) !!} name="{{$name}}" value="{{old($name,isset($value)?$value:null)}}"  />
    <!--end::Input-->
    @if($hint)<span class="form-text text-muted">{{$hint}}</span>@endif
    @error($name)
    <div class="fv-plugins-message-container invalid-feedback help-block has-error">
        {{ $message }}
    </div>
    @enderror
    <span class="fs-7 text-muted text-center w-100 json-error"></span>
</div>
