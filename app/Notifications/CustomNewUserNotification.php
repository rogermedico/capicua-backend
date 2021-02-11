<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class CustomNewUserNotification extends Notification
{


    protected $email;
    protected $password;

    public function __construct($arr){
      $this->email = $arr['email'];
      $this->password = $arr['password'];
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
        $verificationUrl = $this->verificationUrl($notifiable);
        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $verificationUrl
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(Lang::get('New user'))
            ->line('Se us ha afegit com a usuari de la intranet corporativa. Les dades per accedir al vostre compte són les següents:')
            ->line(new HtmlString('<ul><li>Usuari: '.$this->email.'</li><li>Contrasenya: '.$this->password.'</li></ul>'))
            ->line('Tot hi això, no se us permetrà l\'accés fins que confirmeu la vostra adreça electrònica. Per fer-ho, feu clic al botó inferior.')
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return env('FRONTEND_URL').env('FRONTEND_VERIFY_EMAIL').'/'.$notifiable->getKey().'/'.sha1($notifiable->getEmailForVerification());
    }

}
