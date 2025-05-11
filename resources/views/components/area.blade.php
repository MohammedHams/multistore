@props(['disabled' => false,'title'=>'','name'=>'','value'=>''])

<div class="fv-row mb-7 relative  @error($name) fv-plugins-bootstrap5-row-invalid has_error @enderror">
    <!--begin::Label-->
    <label class="{{$attributes->has('required')?'required':''}} fw-bold fs-6 mb-2" for="{{$name}}">{{$title}}</label>
    <!--end::Label-->
    <!--begin::Input-->
    <textarea  {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-control form-control-solid mb-3 mb-lg-0']) !!} id="{{$name}}" name="{{$name}}">{{old($name,isset($value)?$value:null)}}</textarea>
    <!--end::Input-->

    @error($name)
    <div class="fv-plugins-message-container invalid-feedback help-block has-error">
        {{ $message }}
    </div>
    @enderror
</div>
