<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class NewPersonalDocumentNotification extends Notification
{

    protected $user_name;
    protected $document_name;

    public function __construct($arr){
        $this->user_name = $arr['user_name'];
        $this->document_name = $arr['original_name'];
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nou document Personal')
            ->greeting('Hola '.$this->user_name.'!')
            ->line('Tens disponible el següent document personal a la intranet:')
            ->line(new HtmlString('<ul><li>'.$this->document_name.'</li></ul>'))
            ->line('Per consultar-lo accedeix amb les teves credencials i dirigeix-te a l\'apartat els meus documents.');
    }

}
