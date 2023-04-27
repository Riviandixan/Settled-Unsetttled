@component('mail::message')

Mengirim email notification laravel

@component('mail::button', ['url' => $data['url']])
Visit
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
