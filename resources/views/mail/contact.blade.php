<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>{{ __('mail.contact_subject', ['name' => $data['name']]) }}</title>
</head>
<body>
    <h1>{{ __('mail.contact_heading') }}</h1>
    <p><strong>{{ __('contact.form.name') }}:</strong> {{ $data['name'] }}</p>
    <p><strong>{{ __('contact.form.email') }}:</strong> {{ $data['email'] }}</p>
    @if(!empty($data['phone']))
        <p><strong>{{ __('contact.form.phone') }}:</strong> {{ $data['phone'] }}</p>
    @endif
    <p><strong>{{ __('contact.form.message') }}:</strong></p>
    <p>{{ $data['message'] }}</p>
</body>
</html>
