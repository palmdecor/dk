@component('mail::message')
# {{ __('mail.application_submitted_heading') }}

{{ __('mail.application_submitted_body', ['amount' => format_currency($application->loan_amount), 'term' => $application->loan_term]) }}

@component('mail::panel')
**{{ __('application.status') }}:** {{ __('application.status_pending') }}
@endcomponent

{{ __('mail.thank_you') }},<br>
{{ config('app.name') }}
@endcomponent
