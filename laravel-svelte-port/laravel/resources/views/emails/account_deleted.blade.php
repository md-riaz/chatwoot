Account {{ $account->name }} (ID: {{ $account->id }}) has been deleted.

@unless(empty($softDeletedUsers))
The following users were soft-deleted:
@foreach($softDeletedUsers as $u)
- ID: {{ $u['id'] }}, original_email: {{ $u['original_email'] }}
@endforeach
@endunless

If you believe this is in error please contact support.
