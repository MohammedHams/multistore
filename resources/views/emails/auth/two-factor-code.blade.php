@component('mail::message')
# رمز التحقق بخطوتين

مرحباً،

رمز التحقق بخطوتين الخاص بك هو:

@component('mail::panel')
<div style="text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px;">{{ $code }}</div>
@endcomponent

هذا الرمز صالح لمدة 10 دقائق فقط. يرجى عدم مشاركة هذا الرمز مع أي شخص.

شكراً،<br>
{{ config('app.name') }}
@endcomponent
