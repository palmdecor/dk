<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    public function contact(): View
    {
        return view('pages.contact');
    }

    public function submitContact(ContactRequest $request): RedirectResponse
    {
        Mail::to(config('mail.from.address'))->send(new ContactMessage($request->validated()));

        return back()->with('status', __('contact.success'));
    }

    public function kvkk(): View
    {
        return view('pages.kvkk');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }
}
