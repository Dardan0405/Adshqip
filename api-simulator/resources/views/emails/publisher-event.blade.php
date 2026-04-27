<div style="font-family: Arial, sans-serif; font-size: 14px; color: #111827; line-height: 1.6;">
    <p>{{ $mailMessage }}</p>

    @if($actionUrl)
        <p>Action link: <a href="{{ $actionUrl }}">{{ $actionUrl }}</a></p>
    @endif
</div>
