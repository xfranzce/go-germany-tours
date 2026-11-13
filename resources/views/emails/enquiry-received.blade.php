<!DOCTYPE html>
<html lang="en"><body style="font-family:Arial,sans-serif;color:#003049;line-height:1.5">
<h2 style="margin:0 0 8px">New enquiry: {{ $enquiry->formLabel() }}</h2>
<table cellpadding="6" style="border-collapse:collapse">
    <tr><td><b>Name</b></td><td>{{ $enquiry->fullName() }}</td></tr>
    <tr><td><b>Email</b></td><td>{{ $enquiry->email }}</td></tr>
    <tr><td><b>Phone</b></td><td>{{ $enquiry->phone_e164 }}</td></tr>
    @if($enquiry->package_name)<tr><td><b>Package</b></td><td>{{ $enquiry->package_name }}</td></tr>@endif
    @if($enquiry->travel_date)<tr><td><b>Travel date</b></td><td>{{ $enquiry->travel_date }}</td></tr>@endif
    @foreach(['time_of_year' => 'Time of year', 'group_size' => 'Group size', 'nights' => 'Nights', 'second_quote' => 'Second quote?'] as $f => $l)
        @if($enquiry->$f)<tr><td><b>{{ $l }}</b></td><td>{{ $enquiry->$f }}</td></tr>@endif
    @endforeach
    @if($enquiry->special_request)<tr><td><b>Request</b></td><td>{{ $enquiry->special_request }}</td></tr>@endif
    @foreach(($enquiry->quiz_answers ?? []) as $k => $a)<tr><td><b>{{ \Illuminate\Support\Str::headline($k) }}</b></td><td>{{ $a }}</td></tr>@endforeach
    <tr><td><b>Source</b></td><td>{{ $enquiry->source }}</td></tr>
</table>
<p><a href="{{ route('admin.enquiries.show', $enquiry) }}">Open in the admin area</a></p>
</body></html>
