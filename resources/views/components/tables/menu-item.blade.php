@props(['href'=>'#','title'=>''])
<div class="menu-item px-3">
    <a href="{{$href}}" {!! $attributes->merge(['class' => 'menu-link px-3']) !!} >
        {{$title}}
    </a>
</div>
