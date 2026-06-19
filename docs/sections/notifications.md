Saddle surfaces Laravel's database notifications as a bell in the panel sidebar, with an unread badge and one-click mark-as-read. Your application triggers the notifications; Saddle displays them.

### Setup

Add Laravel's `Notifiable` trait to your authenticatable user and run your migrations, Saddle ships the `notifications` table (guarded, so it is harmless if you already created one with `php artisan notifications:table`):

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
}
```

```bash
php artisan migrate
```

### Sending notifications

Send notifications from anywhere in your app over the `database` channel. A notification whose `toArray` returns a `message` (and optionally a `url`) renders cleanly in the bell:

```php
use Illuminate\Notifications\Notification;

class HorseEscaped extends Notification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Cisco has escaped the corral!',
            'url' => '/admin/resources/horses',
        ];
    }
}
```

```php
$user->notify(new HorseEscaped);
```

### In the panel

The bell shows the unread count and the ten most recent notifications. Clicking a notification marks it read and, if it carries a `url`, follows it. **Mark all read** clears the badge. The unread count and recent items are shared with every page, so the bell stays current as you navigate.
