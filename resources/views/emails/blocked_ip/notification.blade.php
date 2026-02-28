<x-mail::message>
# IP Blocked Notification

A new IP address has been successfully blocked by the SecOps Agent.

<x-mail::panel>
**IP Address:** {{ $ip }}
**Agent:** {{ $agent }}
**Time:** {{ $time }}
</x-mail::panel>

**Reason:**
{{ $reason }}

<x-mail::button :url="route('blocked-ips.index')">
View Blocked IPs
</x-mail::button>

Thanks,<br>
{{ config('app.name') }} SecOps Team
</x-mail::message>
